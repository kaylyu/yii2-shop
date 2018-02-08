<?php
/**
 * Admin.php.
 * User: Administrator
 * Date: 2017/9/27 0027
 * Time: 16:21
 * Desc:
 */

namespace app\modules\models;


use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\IdentityInterface;

class Admin extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REG = 'reg';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_SEEKPASS = 'seekpass';
    const SCENARIO_CHANGEPASS = 'changepass';
    const SCENARIO_CHANGEEMAIL = 'changeeamil';

    public $rememberMe = true;
    public $repass;

    public static function tableName()
    {
        return '{{%admin}}';
    }

    public function rules(){
        return [
            ['adminuser', 'required', 'message'=>'管理员账号不能为空', 'on'=>[self::SCENARIO_LOGIN, self::SCENARIO_SEEKPASS, self::SCENARIO_REG]],
            ['adminuser', 'unique', 'message'=>'管理员账号已被注册', 'on'=>[self::SCENARIO_REG]],
            ['adminpass', 'required', 'message'=>'管理员密码不能为空', 'on'=>[self::SCENARIO_LOGIN,self::SCENARIO_CHANGEPASS, self::SCENARIO_REG,self::SCENARIO_CHANGEEMAIL]],
            ['repass', 'required', 'message'=>'确认密码不能为空', 'on'=>[self::SCENARIO_CHANGEPASS, self::SCENARIO_REG]],
            ['repass', 'compare', 'compareAttribute'=>'adminpass', 'message'=>'两次密码不一致', 'on'=>[self::SCENARIO_CHANGEPASS, self::SCENARIO_REG]],
            ['adminemail', 'required', 'message'=>'邮箱不能为空', 'on'=>[self::SCENARIO_SEEKPASS,self::SCENARIO_REG,self::SCENARIO_CHANGEEMAIL]],
            ['adminemail', 'email', 'message'=>'邮箱格式不正确', 'on'=>[self::SCENARIO_SEEKPASS,self::SCENARIO_REG,self::SCENARIO_CHANGEEMAIL]],
            ['adminemail', 'unique', 'message'=>'电子邮箱已被注册', 'on'=>[self::SCENARIO_REG,self::SCENARIO_CHANGEEMAIL]],
            ['rememberMe', 'boolean', 'on'=>[self::SCENARIO_LOGIN]],
            ['adminpass', 'validatePass', 'on'=>[self::SCENARIO_LOGIN,self::SCENARIO_CHANGEEMAIL]],
            ['adminemail', 'validateEamil', 'on'=>[self::SCENARIO_SEEKPASS]],
        ];
    }

    /**
     * 指定新增和更新的场景
     * @see \yii\base\Model::scenarios()
     */
    public function scenarios() {
        return [
            self::SCENARIO_LOGIN => ['adminuser', 'adminpass'],
            self::SCENARIO_REG => ['adminuser', 'adminpass',"adminemail",'repass'],
            self::SCENARIO_UPDATE=>["logintime","loginip"],
            self::SCENARIO_SEEKPASS=>["adminuser","adminemail"],
            self::SCENARIO_CHANGEPASS=>['adminuser',"adminpass",'repass'],
            self::SCENARIO_CHANGEEMAIL=>["adminemail"],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        return $fields;
    }

    public function attributeLabels()
    {
        return [
            'adminuser'=>'管理员账号',
            'adminpass'=>'密码',
            'adminemail'=>'电子邮箱',
            'repass'=>'确认密码',
        ];
    }

    /**
     * 密码验证
     */
    public function validatePass(){
        if( !$this-> hasErrors()){
            $data = self::find()->where('adminuser = :adminuser',[":adminuser"=>$this->adminuser])->one();
            if (is_null($data) || empty($data)){
                $this->addError('adminpass','用户名或密码错误');
                return;
            }
            if(!\Yii::$app->getSecurity()->validatePassword($this->adminpass, $data->adminpass)){
                $this->addError('adminpass','用户名或密码错误');
            }
        }
    }

    /**
     * 邮箱是否是该用户
     */
    public function validateEamil(){
        if(! $this->hasErrors()){
            $data = self::findOne(['adminuser'=>$this->adminuser, 'adminemail'=>$this->adminemail]);
            if (is_null($data) || empty($data)){
                $this->addError('adminemail','管理员电子邮箱不匹配');
            }
        }
    }

    /**
     * 登录成功
     */
    public function login(){
        //更新登录时间及登录IP
        $admin = $this->getAdmin();
        $admin->scenario = self::SCENARIO_UPDATE;
        $admin->logintime = time();
        $admin->loginip = ip2long(\Yii::$app->request->userIP);//$_SERVER['REMOTE_ADDR'];
        if ($admin->update() === false) {
            return false;
        }

        //处理session
        //过期时间
//        $lefttime = $this->rememberMe ? 24*3600 : 0;
//        $session = \Yii::$app->session;
//        //设置session参数
//        session_set_cookie_params($lefttime);
//        //设置session
//        $session['admin'] = [
//            'adminuser' => $this->adminuser,
//            'isLogin' => 1
//        ];
//        return (bool)$session['admin']['isLogin'];
        return \Yii::$app->admin->login($admin,$this->rememberMe ? 24*3600 : 0);
    }

    /**
     * 发送邮件
     */
    public function seekPass(){
        $time = time();
        $token = $this->createToken($this->adminuser, $time);
        $mailer = \Yii::$app->mailer->compose('seekpass',[
            'adminuser'=>$this->adminuser,
            'time'=>$time,
            'token' => $token
        ]);
        $mailer->setFrom(['just_shunjian@163.com'=>'lvfk']);
        $mailer->setTo($this->adminemail);
        $mailer->setSubject('商城-找回密码');
//        return $mailer->send();
        //使用redis异步发送邮件
        return $mailer->queue();
    }

    /**
     * 按照一定规则生成token
     * @param string $adminuser 管理员账号
     * @param int $time 时间戳
     * @return string   返回生成的token
     */
    public function createToken($adminuser, $time){
        $str = 'shop_'.md5($adminuser).base64_encode(\Yii::$app->request->userIP).md5($time);

        return md5($str);
    }

    /**
     * 修改密码
     */
    public function changePass(){
        //由于外层已经对数据进行了验证，即已经执行了validate(),因此在更新时无需再次验证
        $admin = self::findOne(['adminuser'=>$this->adminuser]);
        $admin->scenario = self::SCENARIO_CHANGEPASS;
        $admin->adminpass = \Yii::$app->getSecurity()->generatePasswordHash($this->adminpass);
        return $admin->update(false);//参数false，表明保存的时候不再执行validate()
    }

    /**
     * 修改邮箱
     */
    public function changeemail(){
        return $this->update();
    }

    /**
     * 创建管理员
     * @return bool
     */
    public function reg(){
        //此处添加有两种方式
        //方式一，由于外层已经对数据进行了验证，即已经执行了validate()
        $this->adminpass = \Yii::$app->getSecurity()->generatePasswordHash($this->adminpass);
        $this->createtime = time();
        return $this->save(false);//参数false，表明保存的时候不再执行validate()

        //方式二，不管外层是否验证，此处还是对其进行场景的设置，再执行保持，而save还是会执行validate()
//        $this->scenario = self::SCENARIO_REG;
//        $this->adminpass =  \Yii::$app->getSecurity()->generatePasswordHash($this->adminpass);
//        $this->createtime = time();
//        return $this->save();
    }

    public function getAdmin(){
        return self::findOne(['adminuser'=>$this->adminuser]);
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
        return $this->adminid;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }

    /**
     * 管理员分配权限
     * @param int $adminid  管理员ID
     * @param $children
     * @return bool
     */
    public static function grant($adminid, $children){
        $trans = \Yii::$app->db->beginTransaction();
        try{
            $auth = \Yii::$app->authManager;
            //取消所有授权
            $auth->revokeAll($adminid);
            //分配
            foreach ($children as $item){
                //确定角色或者权限对象类型
                $obj = empty($auth->getRole($item)) ? $auth->getPermission($item) : $auth->getRole($item);
                $auth->assign($obj, $adminid);
            }

            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            return false;
        }

        return true;
    }

    /**
     * 获取管理的角色和权限列表
     * @param  int $adminid 管理员ID
     * @return mixed
     */
    public static function getChildrenByUser($adminid){
        $auth = \Yii::$app->authManager;
        $items = $auth->getRolesByUser($adminid);
        $return = [];
        $return['roles'] = [];
        $return['permissions'] = [];
        foreach ($items as $item){
            $return['roles'][] = $item->name;
        }
        $items = $auth->getPermissionsByUser($adminid);
        foreach ($items as $item){
            $return['permissions'][] = $item->name;
        }
        return $return;
    }
}