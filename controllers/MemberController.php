<?php
/**
 * MemberController.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 17:21
 * Desc:
 */

namespace app\controllers;


use app\models\User;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class MemberController extends  CommonController
{
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'except' => ['regmail','message','qqlogin','qqcallback'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['auth','reg','qqreg','qqcallback'],
                        'roles' => ['?']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout'],
                        'roles' => ['@']
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' =>[
                    'auth' => ['get','post']
                ]
            ]
        ];
    }
    /**
     * 登录
     * @return string
     */
    public function actionAuth(){
        $this->layout = 'layout_second';
        //记录跳转到登录页面的前一个页面的URL
        if(\Yii::$app->request->isGet){
            $referer = \Yii::$app->request->referrer;
            if(empty($referer)){
                $referer = '/';
            }
            \Yii::$app->session->setFlash('backUrl',$referer);
        }

        $model = new User();
        if(\Yii::$app->request->isPost){
            $model->scenario = User::SCENARIO_LOGIN;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                //进行登录
                if($model->login()){
                    $backurl = \Yii::$app->session->getFlash('backUrl');
                    if(empty($backurl)){
                        $backurl = ['/'];
                    }
                    $this->redirect($backurl);
                }else{
                    \Yii::$app->session->setFlash('login_info', '登录失败');
                }
            }
        }

        return $this->render('auth',['model'=>$model]);
    }

    /**
     * 退出登录
     * @return \yii\web\Response
     */
    public function actionLogout(){
        $model = new User();
        if($model->logout()){
            return $this->redirect(\Yii::$app->request->referrer);
        }
    }

    /**
     * 电子邮箱注册发送激活邮件
     * @return string
     */
    public function actionReg(){
        $this->layout = 'layout_second';
        $model = new User();

        if(\Yii::$app->request->isPost){
            $model->scenario = User::SCENARIO_REG_EMAIL;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                //发送邮件
                if($model->replyEmail()){
                    \Yii::$app->session->setFlash('reg_info', '邮件发送成功，请登录到 '.$model->useremail.' 激活');
                }else{
                    \Yii::$app->session->setFlash('reg_info', '邮件发送失败');
                }
            }
        }
        return $this->render('auth',['model'=>$model]);
    }

    /**
     * 认证电子邮箱注册信息，实现电子邮箱注册
     */
    public function actionRegmail(){
        $username = \Yii::$app->request->get('username');
        $time = \Yii::$app->request->get('time');
        $token = \Yii::$app->request->get('token');

        $model = new User();
        if ($model->createToken($username, $time) != $token){
            \Yii::$app->session->setFlash('reg_info', '电子邮件注册激活链接不正确，请重新发送');
            $this->redirect(['auth']);
            \Yii::$app->end();
        }

        if(time() - $time > 5000){
            \Yii::$app->session->setFlash('reg_info', '电子邮件注册激活链接已失效，请重新发送');
            $this->redirect(['auth']);
            \Yii::$app->end();
        }

        //注册
        $model->scenario = User::SCENARIO_REG;
        $model->username = $username;
        $model->userpass = \Yii::$app->request->get('userpass');
        $model->useremail = \Yii::$app->request->get('useremail');

        if($model->regEmail() !== false){
            \Yii::$app->session->setFlash('reg_info', '电子邮箱注册成功');
        }else{
            \Yii::$app->session->setFlash('reg_info', '电子邮箱注册失败');
        }
        $this->redirect(['message']);
        \Yii::$app->end();

    }

    public function actionMessage(){
        $this->layout = 'layout_second';
        return $this->render('message');
    }

    /**
     * QQ注册,写入数据库
     */
    public function actionQqreg(){
        $this->layout = 'layout_second';
        $model = new User();
        if(\Yii::$app->request->isPost){
            $model->scenario = User::SCENARIO_REG_QQ;
            //组装
            $post = \Yii::$app->request->post();
            $post['User']['openid'] = \Yii::$app->session['openid'];
            if($model->load($post) && $model->validate()){
                //QQ用户添加
                if($model->regQqUser() && $model->qqlogin()){
                    return $this->redirect(['/']);
                }else{
                    \Yii::$app->session->setFlash('info', '注册失败');
                }
                $model->userpass = '';
                $model->useremail = '';
                $model->repass = '';
            }
        }

        return $this->render('qqreg', ['model'=>$model]);
    }

    /**
     * 跳转QQ登录页面
     */
    public function actionQqlogin(){
        require_once ("../vendor/qqlogin/qqConnectAPI.php");
        $qc = new \QC();
        $qc->qq_login();
    }

    /**
     * 处理QQ登录之后QQ服务器的回调callback
     */
    public function actionQqcallback(){
        require_once ('../vendor/qqlogin/qqConnectAPI.php');
        //获取登录QQ的信息
        $auth = new \OAuth();
        $access_token = $auth->qq_callback();
        $openid = $auth->get_openid();
        $qc = new \QC($access_token, $openid);
        $userInfo = $qc->get_user_info();

        //写session
        $session = \Yii::$app->session;
        $session['userinfo'] = $userInfo;
        $session['openid'] = $openid;

        //QQ登录前，判断QQ对应用户是否已经在数据库中，否则先插入数据库
        $model = new User;
        $model->openid = $openid;
        if($model->getUserByOpenid() && $model->qqlogin()){//找到
            return $this->redirect(['/']);
        }
        //没找到，先注册
        return $this->redirect(['member/qqreg']);
    }
}