<?php
/**
 * PublicController.php.
 * User: Administrator
 * Date: 2017/9/27 0027
 * Time: 15:01
 * Desc:
 */

namespace app\modules\controllers;


use app\modules\models\Admin;
use yii\web\Controller;

class PublicController extends  Controller
{
    /**
     * 登录
     * @return string
     */
    public function actionLogin(){
        $model = new Admin();
        //判断是否登录
        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_LOGIN;
            if($model->load(\Yii::$app->request->post()) && $model->validate() && $model->login()){
                //进行跳转
                $this->redirect(['default/index']);
                \Yii::$app->end();
            }
        }
        return $this->renderPartial('login', ['model'=>$model]);
    }

    /**
     * 退出
     */
    public function actionLogout(){
//        //删除session
//        \Yii::$app->session->removeAll();
//        if(!isset(\Yii::$app->session['admin']['isLogin'])){
//            //跳转
//            $this->redirect(['login']);
//            \Yii::$app->end();
//        }
        \Yii::$app->admin->logout(false);
        $this->redirect(['/admin/public/login']);
        \Yii::$app->end();
    }

    /**
     * 找回密码、发送邮件
     * @return string
     */
    public function actionSeekpassword(){
        $model = new Admin();
        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_SEEKPASS;
            if($model->load(\Yii::$app->request->post()) && $model->validate() && $model->seekPass()){
                //通知用户电子邮件发送成功
                \Yii::$app->session->setFlash('info', '电子邮件已经发送成功，请查收');
            }
        }
        return $this->renderPartial('seekpassword',['model'=>$model]);
    }
}