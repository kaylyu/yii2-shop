<?php
/**
 * IndexController.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 14:30
 * Desc:
 */

namespace app\controllers;


use app\models\Product;

class IndexController extends  CommonController
{
    public function actionIndex(){
        $this->layout = 'layout_first';
        // 对商品做查询缓存
        $dep = new \yii\caching\DbDependency([
            'sql' => 'select max(updatetime) from {{%product}} where ison = "1"',
        ]);
        $tui = Product::getDb()->cache(function (){
            return Product::find()->where('istui = "1" and ison = "1"')->orderby('createtime desc')->limit(4)->all();
        }, 60, $dep);
        $new = Product::getDb()->cache(function(){
            return Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(4)->all();
        }, 60, $dep);
        $hot = Product::getDb()->cache(function(){
            return Product::find()->where('ison = "1" and ishot = "1"')->orderby('createtime desc')->limit(4)->all();
        }, 60, $dep);
        $sale = Product::getDb()->cache(function(){
            return Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(7)->all();
        }, 60, $dep);

        $data['tui'] = (array)$tui;
        $data['new'] = (array)$new;
        $data['hot'] = (array)$hot;
        $data['all'] = (array)$sale;
        return $this->render("index", ['data' => $data]);
    }

    public function actionError(){
        echo 404;
        exit();
    }
}