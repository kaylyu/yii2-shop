<?php
/**
 * User.php.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 15:45
 * Desc:
 */

namespace app\models;


use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_REG = 'reg';
    const SCENARIO_REG_EMAIL = 'reg_email';
    const SCENARIO_REG_QQ = 'reg_qq';
    const SCENARIO_LOGIN = 'login';


    public $repass;//确认密码
    public $loginname;//登录用户名
    public $rememberMe = true;

    public static function tableName()
    {
        return "{{%user}}";
    }

    public function rules()
    {
        return [
            [['loginname'], 'required', 'message'=>'登录名不能为空', 'on'=> [self::SCENARIO_LOGIN]],
            [['openid'], 'required', 'message'=>'OPENID不能为空', 'on'=> [self::SCENARIO_REG_QQ]],
            [['openid'], 'unique', 'message'=>'OPENID已经存在', 'on'=> [self::SCENARIO_REG_QQ]],
            [['username'], 'required', 'message'=>'用户名不能为空', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_QQ]],
            [['username'], 'unique', 'message'=>'用户名已经存在', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_QQ]],
            [['userpass'], 'required', 'message'=>'密码不能为空', 'on'=> [self::SCENARIO_REG,self::SCENARIO_LOGIN,self::SCENARIO_REG_QQ]],
            ['userpass', 'string', 'min'=>3,'max'=>20, 'on'=> [self::SCENARIO_REG,self::SCENARIO_LOGIN,self::SCENARIO_REG_QQ]],
            [['repass'], 'required', 'message'=>'确认密码不能为空', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_QQ]],
            [['repass'], 'compare', 'compareAttribute'=>'userpass','message'=>'两次密码不一致', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_QQ]],
            [['useremail'], 'required', 'message'=>'电子邮箱不能为空', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_EMAIL]],
            [['useremail'], 'email', 'message'=>'电子邮箱格式不对', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_EMAIL]],
            [['useremail'], 'unique', 'message'=>'电子邮箱已被注册', 'on'=> [self::SCENARIO_REG,self::SCENARIO_REG_EMAIL]],
            ['userpass', 'validatePass', 'on'=>[self::SCENARIO_LOGIN]],

        ];
    }

    public function attributeLabels()
    {
        return [
            'userid' => '用户ID',
            'username' => '用户名',
            'userpass' => '密码',
            'useremail' => '电子邮箱',
            'createtime' => '注册时间',
            'repass' => '确认密码',
            'loginname' => '登录名'
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_REG=>['username','userpass','repass','useremail'],
            self::SCENARIO_REG_EMAIL=>['useremail'],
            self::SCENARIO_REG_QQ=>['username','userpass','repass','openid'],
            self::SCENARIO_LOGIN=>['loginname','userpass','rememberMe'],
        ];
    }

    /**
     * 验证密码
     */
    public function validatePass(){
        if(! $this->hasErrors()){
            //此处要处理用户名和邮箱登录
            $loginname = 'username';
            if(preg_match('/@/', $this->loginname)){
                $loginname = 'useremail';
            }
            $user = self::find()->where($loginname.'=:loginname',[':loginname'=>$this->loginname])->one();
            if(empty($user)){
                $this->addError('userpass', '登录名或者密码错误');
                return ;
            }
            if(!\Yii::$app->getSecurity()->validatePassword($this->userpass, $user->userpass)){
                $this->addError('userpass', '登录名或者密码错误');
            }
        }
    }

    public function getProfile(){
        return $this->hasOne(Profile::className(), ['userid'=>'userid']);
    }


    /**
     * 普通登录(用户或者邮箱)
     * @return bool
     */
    public function login(){
        $trans = \Yii::$app->db->beginTransaction();
        try{
            //更新登录时间及IP
            $user = $this->getUser();
            if(!$user){
                throw new Exception('登录失败，重新登录');
            }
            $user->uptime = time();
            $user->loginip = ip2long(\Yii::$app->request->userIP);
            $update =  $user->update(false);
            if($update === false){
                throw new Exception('更新用户登录信息失败');
            }
            $trans->commit();

            //获取过期时间
//            $session = \Yii::$app->session;
//            $lefttime = $this->rememberMe ? 24*3600 : 0;
//            //写入到cookie
//            session_set_cookie_params($lefttime);
//
//            //写session
//            $session['loginname'] = $this->loginname;
//            $session['isLogin'] = 1;

            return \Yii::$app->user->login($user, $this->rememberMe ? 24*3600 : 0);

        }catch (Exception $e ){
            $trans->rollBack();
        }

        return false;
    }

    /**
     * 普通登录(用户或者邮箱)
     * @return bool
     */
    public function qqlogin(){
        $trans = \Yii::$app->db->beginTransaction();
        try{
            //更新登录时间及IP
            $user = $this->getUserByOpenid();
            if(!$user){
                throw new Exception('登录失败，重新登录');
            }
            $user->uptime = time();
            $user->loginip = ip2long(\Yii::$app->request->userIP);
            $update =  $user->update(false);
            if($update === false){
                throw new Exception('更新用户登录信息失败');
            }
            $trans->commit();

            return \Yii::$app->user->login($user, $this->rememberMe ? 24*3600 : 0);

        }catch (Exception $e ){
            $trans->rollBack();
        }

        return false;
    }

    /**
     * 退出登录
     * @return bool
     */
    public function logout(){
//        $session = \Yii::$app->session;
//        $session->remove('loginname');
//        $session->remove('isLogin');
//        return !isset($session['isLogin']);
        return \Yii::$app->user->logout(false);
    }

    /**
     * 注册用户
     */
    public function regUser(){
        $this->userpass = $this->_createPass($this->userpass);
        $this->createtime = time();
        return $this->save(false);
    }

    /**
     * 邮箱注册，发送邮件
     */
    public function replyEmail(){
        //定义邮箱注册的随机用户名和密码
        $username = 'shop_'.uniqid();
        $userpass = uniqid();

        $time = time();
        $token = $this->createToken($username, $time);
        //直接发送邮件
//        return \Yii::$app->mailer->compose('reply', [
//                'username'=>$username,
//                'userpass'=>$userpass,
//                'useremail'=>$this->useremail,
//                'time'=>$time,
//                'token'=>$token
//            ])
//            ->setSubject('商城-电子邮箱注册激活')
//            ->setFrom(['just_shunjian@163.com'=>'lvfk'])
//            ->setTo($this->useremail)
//            ->send();
        //先发送到redis，异步发送
        return \Yii::$app->mailer->compose('reply', [
            'username'=>$username,
            'userpass'=>$userpass,
            'useremail'=>$this->useremail,
            'time'=>$time,
            'token'=>$token
        ])
            ->setSubject('商城-电子邮箱注册激活')
            ->setFrom(['just_shunjian@163.com'=>'lvfk'])
            ->setTo($this->useremail)
            ->queue();
    }

    /**
     * 邮箱注册
     */
    public function regEmail(){
        $this->userpass = $this->_createPass($this->userpass);
        $this->createtime = time();
        try{
            return $this->save(false);
        }catch (Exception $e){
            return false;
        }

    }

    /**
     * QQ注册用户
     */
    public function regQqUser(){
        $this->userpass = $this->_createPass($this->userpass);
        $this->createtime = time();
        return $this->save(false);

    }

    /**
     * 密码加密
     * @param string $password 用户密码
     * @return string   加密后的用户密码
     */
    private function _createPass($password){
//        return md5('shop_'.$password);
        return \Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * 按照一定规则生成token
     * @param string $username 用户账号
     * @param int $time 时间戳
     * @return string   返回生成的token
     */
    public function createToken($username, $time){
        $str = 'shop_user_'.md5($username).base64_encode(\Yii::$app->request->userIP).md5($time);

        return md5($str);
    }

    public function getUser(){
        return self::find()->where('username=:loginname or useremail=:loginname',[':loginname'=>$this->loginname])->one();
    }

    public function getUserByOpenid(){
        return self::find()->where('openid=:openid', [':openid'=>$this->openid])->one();
    }

    public static function findIdentity($id)
    {
        return self::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return $this->userid;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }
}