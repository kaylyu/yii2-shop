<?php
/**
 * UserController.php.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 15:41
 * Desc:
 */

namespace app\modules\controllers;


use app\models\Profile;
use app\models\User;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;

class UserController extends CommonController
{
    public $layout = 'layout_admin';

    /**
     * 列举用户
     * @return string
     */
    public function actionUsers(){
        $model = User::find();
        $count = $model->count();
        $pager = new Pagination(['totalCount'=>$count, 'pageSize'=>\Yii::$app->params['pageSize']['user']]);
        $users = $model->offset($pager->offset)->limit($pager->limit)->all();
        return $this->render('users',['pager'=>$pager,'users'=>$users]);
    }

    /**
     * 添加用户
     * @return string
     */
    public function actionUseradd(){
        $model = new User();

        if(\Yii::$app->request->isPost){
            $model->scenario = User::SCENARIO_REG;
            if($model->load(\Yii::$app->request->post()) && $model->validate()){
                //进行用户添加
                if($model->regUser() !== false){
                    \Yii::$app->session->setFlash('info', '添加成功');
                }else{
                    \Yii::$app->session->setFlash('info', '添加失败');
                }
                $model->userpass = '';
                $model->useremail = '';
                $model->repass = '';
            }
        }
        return $this->render('reg', ['model'=>$model]);
    }

    /**
     * 删除用户
     * @param $userid
     */
    public function actionDel($userid){
        if(!is_numeric($userid)){//数据校验
            $this->redirect(['users']);
            \Yii::$app->end();
        }
        $model = User::findOne($userid);
        if($model === null){
            $this->redirect(['users']);
            \Yii::$app->end();
        }

        $trans = \Yii::$app->db->beginTransaction();
        try{
            //进行删除
            $profile = Profile::find()->where('userid=:userid',[':userid'=>$userid])->one();
            if($profile){
                //删除
                $res = Profile::deleteAll('userid=:userid',[':userid'=>$userid]);
                if(empty($res)){
                    throw new Exception('profile 删除失败');
                }
            }
            if(!$model->delete()){
                throw new Exception('user 删除失败');
            }
            $trans->commit();
            \Yii::$app->session->setFlash('info', $model->username.' 删除成功');
        }catch (Exception $e){
            $trans->rollBack();
            \Yii::$app->session->setFlash('info', $model->username.' 删除失败');
        }


        $this->redirect(['users']);
        \Yii::$app->end();
    }
}