<?php
/**
 * CategoryController.php.
 * User: lvfk
 * Date: 2017/12/23 0023
 * Time: 15:09
 * Desc:
 */

namespace app\modules\controllers;


use app\models\Category;
use yii\web\MethodNotAllowedHttpException;
use yii\web\Response;

class CategoryController extends CommonController
{
    public $layout = 'layout_admin';

    /**
     * 返回JSON
     */
    public function actionTree(){
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $model = new Category();
        $data = $model->getPrimaryCate();
        if(!empty($data)){
            return $data['data'];
        }
        return [];
    }


    public function actionList(){

//        $model = new Category();
//        $cates = $model->getTreeList();
//        return $this->render('cates',[
//            'cates'=>$cates
//        ]);

        $page = \Yii::$app->request->get('page')? intval(\Yii::$app->request->get('page')) : 1;
        $perpage = \Yii::$app->request->get('per-page') ? intval(\Yii::$app->request->get('per-page')) : 10;
        $model = new Category();
        $data = $model->getPrimaryCate();
        return $this->render('cates',[
            'pager'=>$data['pager'],
            'page'=>$page,
            'perpage'=>$perpage,
        ]);
    }

    public function actionAdd(){
        $model = new Category();
        if(\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            if($model->add($post)){
                \Yii::$app->session->setFlash('info','添加成功');
            }
            $model->title = '';
        }
        $tree = $model->getOptions();
        return $this->render('add',[
            'model'=>$model,
            'list'=>$tree
        ]);
    }

    public function actionMod($cateid){
        $model = Category::findOne($cateid);
        if(\Yii::$app->request->isPost){
            $post = \Yii::$app->request->post();
            if($model->mod($post)){
                \Yii::$app->session->setFlash('info','更新成功');
            }
            $model->title = '';
        }
        $tree = $model->getOptions();
        return $this->render('add',[
            'model'=>$model,
            'list'=>$tree
        ]);
    }

    public function actionDel($cateid){
        try{
            if(($model = Category::findOne($cateid)) !== null){
                //判断是否存在子分类
                $pid = $model->cateid;
                $data = Category::find()->where('parentid=:parentid',[':parentid'=>$pid])->one();
                if(!empty($data)){
                    throw new \Exception('该分类下有子分类');
                }
                if($model->delete()){
                    throw new \Exception('删除成功');
                }else{
                    throw new \Exception('删除失败');
                }
            }else{
                throw new \Exception('参数不存在');
            }
        }catch (\Exception $e){
            \Yii::$app->session->setFlash('info',$e->getMessage());
        }

        return $this->redirect(['list']);
    }

    /**
     * 重命名
     * @return array
     * @throws MethodNotAllowedHttpException
     */
    public function actionRename(){
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if(!\Yii::$app->request->isAjax){
            throw new MethodNotAllowedHttpException('Access Denied');
        }
        $post = \Yii::$app->request->post();
        $newtext = $post['new'];
        $old = $post['old'];
        $id = $post['id'];
        if(empty($newtext) || empty($id)){
            return ['code' => -1, 'message'=>'参数为空', 'data'=>[]];
        }

        if($newtext == $old){
            return ['code' => 0, 'message'=>'ok', 'data'=>[]];
        }

        $model = Category::findOne($id);
        $model->scenario = 'rename';
        $model->title = $newtext;
        if($model->update()){
            return ['code' => 0, 'message'=>'ok', 'data'=>[]];
        }
        return ['code' => -2, 'message'=>'更新失败', 'data'=>[]];
    }

    /**
     * 删除
     * @return array
     * @throws MethodNotAllowedHttpException
     */
    public function actionDelete(){
        \Yii::$app->response->format = Response::FORMAT_JSON;
        if(!\Yii::$app->request->isAjax){
            throw new MethodNotAllowedHttpException('Access Denied');
        }

        try{
            $cateid = \Yii::$app->request->get('id');
            if(empty($cateid)){
                return ['code' => -1, 'message'=>'参数为空', 'data'=>[]];
            }

            if(($model = Category::findOne($cateid)) !== null){
                //判断是否存在子分类
                $pid = $model->cateid;
                $data = Category::find()->where('parentid=:parentid',[':parentid'=>$pid])->one();
                if(!empty($data)){
                    throw new \Exception('该分类下有子分类');
                }
                if(!$model->delete()){
                    throw new \Exception('删除失败');
                }
            }else{
                throw new \Exception('查询失败');
            }
        }catch (\Exception $e){
            return ['code' => -1, 'message'=>$e->getMessage(), 'data'=>[]];
        }

        return ['code' => 0, 'message'=>'ok', 'data'=>[]];
    }
}