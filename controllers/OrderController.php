<?php
/**
 * OrderController.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 16:54
 * Desc:
 */

namespace app\controllers;

use app\models\Address;
use app\models\Cart;
use app\models\Order;
use app\models\OrderDetail;
use app\models\Product;
use app\models\User;
use dzer\express\Express;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\HttpException;

class OrderController extends CommonController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['*'],
//                'except' => [],
                'rules' => [
//                    [
//                        'allow' => false,
//                        'actions' => [],
//                        'roles' => ['?'],//guest
//                    ],
                    [
                        'allow' => true,
                        'actions' => ['index','check','add','confirm','pay','getexpress','received'],
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' =>[
                    'confirm' => ['post'],
                    'add' => ['post']
                ]
            ]
        ];
    }

    public function actionIndex(){
        $this->layout = 'layout_second';
        //获取用户ID
        $userid = \Yii::$app->user->id;
        $orders = Order::getProducts($userid);
        return $this->render('index',['orders'=>$orders]);
    }

    public function actionCheck(){
        $this->layout = 'layout_first';
        //查询订单信息
        $orderid = \Yii::$app->request->get('orderid');
        $status = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one()->status;
        if ($status != Order::CREATEORDER && $status != Order::CHECKORDER) {
            return $this->redirect(['order/index']);
        }

        //查询通信地址
        $userid = \Yii::$app->user->id;
        $addresses = Address::find()->where('userid=:userid',[":userid"=>$userid])->asArray()->all();
        $details = OrderDetail::find()->where('orderid=:orderid',[":orderid"=>$orderid])->asArray()->all();
        $data = [];
        foreach($details as $detail) {
            $model = Product::find()->where('productid = :pid' , [':pid' => $detail['productid']])->one();
            $detail['title'] = $model->title;
            $detail['cover'] = $model->cover;
            $data[] = $detail;
        }
        $express = \Yii::$app->params['express'];
        $expressPrice = \Yii::$app->params['expressPrice'];
        return $this->render("check", ['express' => $express, 'expressPrice' => $expressPrice, 'addresses' => $addresses, 'products' => $data]);
    }

    public function actionAdd(){

        $transaction = \Yii::$app->db->beginTransaction();
        try{
            //插入Order表
            if(\Yii::$app->request->isPost){
                $post = \Yii::$app->request->post();
                $ordermodel = new Order();
                $ordermodel->scenario = Order::SCENARIO_ADD;

                $ordermodel->userid = \Yii::$app->user->id;
                $ordermodel->status = Order::CREATEORDER;
                $ordermodel->createtime = time();
                if (!$ordermodel->save()) {
                    throw new \Exception('订单添加失败');
                }
                //获取主键ID
                $orderid = $ordermodel->getPrimaryKey();
                foreach ($post['OrderDetail'] as $product) {
                    $model = new OrderDetail();
                    $product['orderid'] = $orderid;
                    $product['createtime'] = time();
                    $data['OrderDetail'] = $product;
                    if (!$model->add($data)) {
                        throw new \Exception();
                    }
                    Cart::deleteAll('productid = :pid' , [':pid' => $product['productid']]);
                    Product::updateAllCounters(['num' => -$product['productnum']], 'productid = :pid', [':pid' => $product['productid']]);
                }
            }
            $transaction->commit();
        }catch(\Exception $e) {
            $transaction->rollback();
            return $this->redirect(['cart/index']);
        }
        return $this->redirect(['order/check', 'orderid' => $orderid]);
    }

    public function actionConfirm()
    {
        //addressid, expressid(邮递), status, amount(orderid,userid)
        try {
            $post = \Yii::$app->request->post();

            $userid = \Yii::$app->user->id;
            //查找用户的订单
            $model = Order::find()->where('orderid = :oid and userid = :uid', [':oid' => $post['orderid'], ':uid' => $userid])->one();
            if (empty($model)) {
                throw new \Exception('查找用户的订单');
            }
            $model->scenario = Order::SCENARIO_UPDATE;
            $post['status'] = Order::CHECKORDER;
            $details = OrderDetail::find()->where('orderid = :oid', [':oid' => $post['orderid']])->all();
            $amount = 0;
            foreach($details as $detail) {
                $amount += $detail->productnum*$detail->price;
            }
            if ($amount <= 0) {
                throw new \Exception('总金额小于零');
            }
            $express = \Yii::$app->params['expressPrice'][$post['expressid']];
            if ($express < 0) {
                throw new \Exception('传递ID小于零');
            }
            $amount += $express;
            $post['amount'] = $amount;
            $data['Order'] = $post;
            if (empty($post['addressid'])) {
                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
            }
            if ($model->load($data) && $model->save()) {
                return $this->redirect(['order/pay', 'orderid' => $post['orderid'], 'paymethod' => $post['paymethod']]);
            }
        }catch(\Exception $e) {
            var_dump($e->getMessage());
        }
        return $this->redirect(['index/index']);
    }

    /**
     * 支付宝支付
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionPay()
    {
        try{
            $orderid = \Yii::$app->request->get('orderid');
            $paymethod = \Yii::$app->request->get('paymethod');
            if (empty($orderid) || empty($paymethod)) {
                throw new \Exception('参数为空');
            }
            //没有支付宝帐号，默认提交就支付成功
//            if ($paymethod == 'alipay') {
//                return Pay::alipay($orderid);
//            }

            $order_info = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
            if (!$order_info) {
                throw new \Exception('订单不存在');
            }
            if ($order_info->status == Order::CHECKORDER) {
                $status = Order::PAYSUCCESS;
                Order::updateAll(['status' => $status, 'tradeno' => date('YmdHis',time()).mt_rand(10000,99999),'tradeext' => json_encode($order_info)], 'orderid = :oid', [':oid' => $order_info->orderid]);
            } else {
                throw new \Exception('订单更新失败');
            }
            return $this->redirect(['order/index']);

        }catch(\Exception $e) {
            throw new HttpException($e->getMessage());
        }
    }

    /**
     * 查询物流状态
     */
    public function actionGetexpress()
    {
        $expressno = \Yii::$app->request->get('expressno');
        $res = Express::search($expressno);
        echo $res;
        exit;
    }

    /**
     * 用户确认收货
     * @return \yii\web\Response
     */
    public function actionReceived()
    {
        $orderid = \Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        if (!empty($order) && $order->status == Order::SENDED) {
            $order->status = Order::RECEIVED;
            $order->save();
        }
        return $this->redirect(['order/index']);
    }

}