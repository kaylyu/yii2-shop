<?php
/**
 * AuthorRule.php.
 * User: lvfk
 * Date: 2018/1/29 0029
 * Time: 15:53
 * Desc:
 */

namespace app\models;


use yii\rbac\Rule;

class AuthorRule extends Rule
{
    public $name = 'isAuthor';

    public function execute($user, $item, $params)
    {
        //获取控制器方法名称
        $action = \Yii::$app->controller->action->id;
        if($action == 'delete'){//验证当前登录用户ID是否是分类创建的用户ID
            $cateid = \Yii::$app->request->get('id');
            $cate = Category::findOne($cateid);
            return $cate->adminid == $user;//当前登录用户ID是否是分类创建的用户ID
        }

        return true;//有权限
    }
}