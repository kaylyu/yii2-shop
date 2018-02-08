<?php
/**
 * DefaultController.php.
 * User: Administrator
 * Date: 2017/9/27 0027
 * Time: 13:48
 * Desc:
 */

namespace app\modules\controllers;


class DefaultController extends CommonController
{
    public function actionIndex(){
        $this->layout = 'layout_admin';
        return $this->render('index');
    }
}