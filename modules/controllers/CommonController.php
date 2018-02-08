<?php

namespace app\modules\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UnauthorizedHttpException;

class CommonController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'admin',//必须指定，否则还是调用前台的User
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

//    public function init()
//    {
//        //已经登录过的直接访问主页
//        if(Yii::$app->admin->isGuest){
//            //跳转
//            $this->redirect(['/admin/public/login']);
//            \Yii::$app->end();
//        }
//    }

    /**
     * 登录之后RBAC权限验证
     * @param \yii\base\Action $action
     * @return bool
     * @throws UnauthorizedHttpException
     */
    public function beforeAction($action)
    {
        //如果父类就是打断流程,那么直接返回false
        if(!parent::beforeAction($action)){
            return false;
        }

        //验证后台登录者的访问权限
        //通过组建User配置项来完成，由于后台的User组建名为admin(admin为web.php中配置的后台用户登录组建名)
        //控制器名称
        $controller = $action->controller->id;
        //action方法名称
        $actionName = $action->id;
        //验证控制器的访问权限
        if(\Yii::$app->admin->can($controller.'/*')){
            return true;//通过验证，继续执行后续流程
        }

        //验证控制器中action的访问权限
        if(\Yii::$app->admin->can($controller.'/'.$actionName)){
            return true;//通过验证，继续执行后续流程
        }

        //没有权限访问抛出异常
        throw  new UnauthorizedHttpException('您对 '.$controller.'/'.$actionName.' 没有权限访问');
//        return true;
    }
}
