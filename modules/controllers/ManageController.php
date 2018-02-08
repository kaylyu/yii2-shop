<?php
/**
 * ManageController.php.
 * User: Administrator
 * Date: 2017/9/28 0028
 * Time: 13:54
 * Desc:
 */

namespace app\modules\controllers;


use app\modules\models\Admin;
use app\modules\models\AuthItem;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ManageController extends CommonController
{
    /**
     * 修改密码
     * @return string
     */
    public function actionMailchangepass(){
        $time = \Yii::$app->request->get('timestamp');
        $adminuser = \Yii::$app->request->get('adminuser');
        $token = \Yii::$app->request->get('token');

        $model = new Admin();
        $myToken = $model->createToken($adminuser, $time);
        if($myToken != $token){
            $this->redirect(['public/login']);
            \Yii::$app->end();
        }

        //进行时间校验
        if(time() - $time > 3000){
            $this->redirect(['public/login']);
            \Yii::$app->end();
        }

        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_CHANGEPASS;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                if($model->changePass() !== false){
                    \Yii::$app->session->setFlash('info', '密码修改成功，请用新密码登录');
                }else{
                    \Yii::$app->session->setFlash('info', '密码修改失败');
                }
            }
        }

        $model->adminuser = $adminuser;

        //密码重置
        return $this->renderPartial('mailchangepass', ['model'=>$model]);

    }

    /**
     * 管理员列表
     * @return string
     */
    public function actionManagers(){
        $this->layout = 'layout_admin';
        $model = Admin::find();
        $count = $model->count();
        $pageSize = \Yii::$app->params['pageSize']['manage'];
        $pager = new Pagination(['totalCount'=>$count, 'pageSize'=>$pageSize]);
        $managers = $model->offset($pager->offset)->limit($pager->limit)->all();
        return $this->render('managers',['managers'=>$managers,'pager'=>$pager]);
    }

    /**
     * 创建管理员
     * @return string
     */
    public function actionReg(){
        $this->layout = 'layout_admin';
        $model = new Admin();
        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_REG;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                if($model->reg()){
                    \Yii::$app->session->setFlash('info', '创建成功');
                    $model->adminuser = '';
                    $model->adminpass = '';
                    $model->adminemail = '';
                    $model->repass = '';
                }else{
                    \Yii::$app->session->setFlash('info', '创建失败');
                }
            }
        }

        return $this->render('reg',['model'=>$model]);
    }

    /**
     * 删除
     * @param $adminid
     */
    public function actionDel($adminid){
        if(!is_numeric($adminid)){//数据校验
            $this->redirect(['managers']);
            \Yii::$app->end();
        }
        $model = Admin::findOne($adminid);
        if($model === null){
            $this->redirect(['managers']);
            \Yii::$app->end();
        }
        \Yii::$app->session->setFlash('info', $model->adminuser.' 删除成功');
        $model->delete();
        $this->redirect(['managers']);
        \Yii::$app->end();
    }

    /**
     * 修改邮箱
     * @return string
     */
    public function actionChangeemail(){
        $this->layout = 'layout_admin';
        $model = Admin::find()->where('adminuser= :adminuser',[':adminuser'=>\Yii::$app->admin->identity->adminuser])->one();
        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_CHANGEEMAIL;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                if($model->changeemail()){
                    \Yii::$app->session->setFlash('info', '修改成功');
                }else{
                    \Yii::$app->session->setFlash('info', '修改失败');
                }
            }
        }
        $model->adminpass = '';

        return $this->render('changeemail',['model'=>$model]);
    }

    /**
     * 修改密码
     * @return string
     */
    public function actionChangepass(){
        $this->layout = 'layout_admin';
        $model = Admin::find()->where('adminuser= :adminuser',[':adminuser'=>\Yii::$app->admin->identity->adminuser])->one();
        if(\Yii::$app->request->isPost){
            $model->scenario = Admin::SCENARIO_CHANGEPASS;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                if($model->changepass() !== false){
                    \Yii::$app->session->setFlash('info', '修改成功');
                }else{
                    \Yii::$app->session->setFlash('info', '修改失败');
                }
            }
        }
        $model->adminpass = '';
        $model->repass = '';

        return $this->render('changepass',['model'=>$model]);
    }

    /**
     * 分配权限
     */
    public function actionAssign($adminid){
        $this->layout = 'layout_admin';
        $adminid = intval($adminid);
        if(empty($adminid)){
            throw new Exception('参数非法');
        }

        $admin = Admin::findOne($adminid);
        if(empty($admin)){
            throw  new NotFoundHttpException('admin not found');
        }

        //表单提交
        if(\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            $children = !empty($post['children']) ? $post['children'] : [];
            if(Admin::grant($adminid, $children)){
                \Yii::$app->session->setFlash('info','授权成功');
            }
        }

        $auth = \Yii::$app->authManager;
        $roles = AuthItem::getOptions($auth->getRoles(),null);
        $permissions = AuthItem::getOptions($auth->getPermissions(),null);
        $children = Admin::getChildrenByUser($adminid);
        return $this->render('assignitem',
            [
                'admin'=>$admin->adminuser,
                'roles'=>$roles,
                'permissions'=>$permissions,
                'children'=>$children,
            ]);
    }
}