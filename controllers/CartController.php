<?php
/**
 * CartController.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 16:40
 * Desc:
 */

namespace app\controllers;

use app\models\Cart;
use app\models\Product;
use app\models\User;
use yii\filters\AccessControl;

class CartController extends CommonController
{
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    public function actionIndex(){
        $this->layout = 'layout_first';
        $userid = \Yii::$app->user->id;
        $cart = Cart::find()->where('userid = :uid', [':uid' => $userid])->asArray()->all();
        $data = [];
        foreach ($cart as $k=>$pro) {
            $product = Product::find()->where('productid = :pid', [':pid' => $pro['productid']])->one();
            $data[$k]['cover'] = $product->cover;
            $data[$k]['title'] = $product->title;
            $data[$k]['productnum'] = $pro['productnum'];
            $data[$k]['price'] = $pro['price'];
            $data[$k]['productid'] = $pro['productid'];
            $data[$k]['cartid'] = $pro['cartid'];
        }
        return $this->render("index", ['data' => $data]);
    }

    public function actionAdd()
    {
        $userid = \Yii::$app->user->id;
        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $num = \Yii::$app->request->post()['productnum'];
            $data['Cart'] = $post;
            $data['Cart']['userid'] = $userid;
        }
        if (\Yii::$app->request->isGet) {
            $productid = \Yii::$app->request->get("productid");
            $model = Product::find()->where('productid = :pid', [':pid' => $productid])->one();
            $price = $model->issale ? $model->saleprice : $model->price;
            $num = 1;
            $data['Cart'] = ['productid' => $productid, 'productnum' => $num, 'price' => $price, 'userid' => $userid];
        }
        if (!$model = Cart::find()->where('productid = :pid and userid = :uid', [':pid' => $data['Cart']['productid'], ':uid' => $data['Cart']['userid']])->one()) {
            $model = new Cart;
        } else {
            $data['Cart']['productnum'] = $model->productnum + $num;
        }
        $model->load($data);
        $model->save();
        return $this->redirect(['cart/index']);
    }

    public function actionMod()
    {
        $cartid = \Yii::$app->request->get("cartid");
        $productnum = \Yii::$app->request->get("productnum");
//        Cart::updateAll(['productnum' => $productnum], 'cartid = :cid', [':cid' => $cartid]);

        $cart = Cart::findOne($cartid);
        $cart->productnum = $productnum;
        $cart->update();
    }

    public function actionDel()
    {
        $cartid = \Yii::$app->request->get("cartid");
//        Cart::deleteAll('cartid = :cid', [':cid' => $cartid]);
        $cart = Cart::findOne($cartid);
        $cart->delete();
        return $this->redirect(['cart/index']);
    }
}