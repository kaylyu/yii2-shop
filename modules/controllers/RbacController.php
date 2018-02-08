<?php
/**
 * RbacController.php.
 * User: lvfk
 * Date: 2018/1/19 0019
 * Time: 15:43
 * Desc:
 */

namespace app\modules\controllers;

use app\modules\models\AuthItem;
use app\modules\models\AuthItemSearch;
use app\modules\models\AuthRuleSearch;
use Qiniu\Auth;
use yii\web\NotFoundHttpException;

/**
 * RBAC 控制器
 * @package app\modules\controllers
 */
class RbacController extends CommonController
{
    public $layout = 'layout_admin';
    public function actionCreaterole(){
        if(\Yii::$app->request->isPost){
            //获取数据，并交验
            $post = \Yii::$app->request->post();
            if(empty($post['name']) || empty($post['description'])){
                throw new NotFoundHttpException('参数错误');
            }
            $auth = \Yii::$app->authManager;
            //创建角色
            $role = $auth->createRole(null);
            $role->name = $post['name'];
            $role->description = $post['description'];
            $role->ruleName = (isset($post['rule_name']) && !empty($post['rule_name'])) ? $post['rule_name'] : null;
            $role->data = (isset($post['data']) && !empty($post['data'])) ? $post['data'] : null;
            //写入数据库
            if($auth->add($role)){
                \Yii::$app->session->setFlash('info','添加成功');
            }

        }
        return $this->render('createitem');
    }

    /**
     * 角色列表
     * @return string
     */
    public function actionRoles(){
        $searchModel = new AuthItemSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        return $this->render('items',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 给角色分配权限或者子角色权限
     * @param string $name 父节点角色
     * @return string
     */
    public function actionAssignitem($name){
        //安全处理，防止SQL注入
        $name = htmlentities($name);

        //针对表单提交
        if(\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            if(AuthItem::addChild($post['children'], $name)){
                \Yii::$app->session->setFlash('info','分配成功');
            }else{
                \Yii::$app->session->setFlash('info','分配失败');
            }
        }

        $auth = \Yii::$app->authManager;
        //根据角色名字获取角色信息
        $parent = $auth->getRole($name);
        //获取可以分配的角色(剔除已经持有的角色)
        $roles = AuthItem::getOptions($auth->getRoles(),$parent);
        //获取可以分配的权限(剔除已经持有的权限)
        $permissions = AuthItem::getOptions($auth->getPermissions(),$parent);
        //获取角色拥有的子角色和权限
        $children = AuthItem::getChildrenByName($name);
        return $this->render('assignitem',
            [
                'parent'=>$name,
                'roles'=>$roles,
                'permissions'=>$permissions,
                'children' =>$children
            ]);
    }

    /**
     * 规则列表
     * @return string
     */
    public function actionRules(){
        $searchModel = new AuthRuleSearch();
        $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
        return $this->render('rules',[
           'searchModel' => $searchModel,
           'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 创建规则
     */
    public function actionCreaterule(){
        if(\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            if(empty($post['clazz'])){
                throw new NotFoundHttpException('参数错误');
            }

            $className = "app\\models\\".$post['clazz'];
            if(!class_exists($className)){
                throw new NotFoundHttpException('规则类不存在');
            }

            $rule = new $className;
            //添加规则
            if(\Yii::$app->authManager->add($rule)){
                \Yii::$app->session->setFlash('info','添加规则成功');
            }
        }

        return $this->render('createrule');
    }
}