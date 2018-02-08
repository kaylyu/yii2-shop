<?php

namespace app\modules\controllers;
use app\models\Order;
use yii\data\Pagination;
use Yii;

class OrderController extends CommonController
{
    public function actionList()
    {
        $this->layout = "layout_admin";
        $model = Order::find();
        $count = $model->count();
        $pageSize = Yii::$app->params['pageSize']['order'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $data = $model->offset($pager->offset)->limit($pager->limit)->all();
        $data = Order::getDetail($data);
        return $this->render('list', ['pager' => $pager, 'orders' => $data]);
    }

    public function actionDetail()
    {
        $this->layout = "layout_admin";
        $orderid = (int)Yii::$app->request->get('orderid');
        $order = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        $data = Order::getData($order);
        return $this->render('detail', ['order' => $data]);
    }

    public function actionSend()
    {
        $this->layout = "layout_admin";
        $orderid = (int)Yii::$app->request->get('orderid');
        $model = Order::find()->where('orderid = :oid', [':oid' => $orderid])->one();
        $model->scenario = Order::SCENARIO_SEND;
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            $model->status = Order::SENDED;
            if ($model->load($post) && $model->save()) {
                Yii::$app->session->setFlash('info', '发货成功');
            }
        }
        return $this->render('send', ['model' => $model]);
    }

}







