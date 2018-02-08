<?php

namespace app\controllers;
use app\controllers\CommonController;
use app\models\Pay;
use Yii;
use yii\filters\AccessControl;

class PayController extends CommonController
{
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' =>[
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    public $enableCsrfValidation = false;

    /**
     * 支付宝异步通知
     * 注意：由于支付宝异步通知不能包含？等get参数，因此需要中间转换一次
     * 比如，我们在支付宝异步通知中填写一个我们自己的php页面，由这个php页面跳转到yii2框架中的这个notify页面
     * 比如中间php页面可以是：http://shop.xxx.com/notify.php,再由这个页面到下面notify页面
     */
    public function actionNotify()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if (Pay::notify($post)) {
                echo "success";
                exit;
            }
            echo "fail";
            exit;
        }
    }

    /**
     * 支付宝支付成功跳转页面
     * @return string
     */
    public function actionReturn()
    {
        $this->layout = 'layout_first';
        $status = Yii::$app->request->get('trade_status');
        if ($status == 'TRADE_SUCCESS') {
            $s = 'ok';
        } else {
            $s = 'no';
        }
        return $this->render("status", ['status' => $s]);
    }
}





