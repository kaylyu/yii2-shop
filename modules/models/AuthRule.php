<?php
/**
 * Roles.php.
 * User: lvfk
 * Date: 2018/1/24 0024
 * Time: 17:36
 * Desc:
 */

namespace app\modules\models;


use yii\db\ActiveRecord;
use yii\db\Exception;

class AuthRule extends ActiveRecord
{
    public static function  tableName()
    {
        return "{{%auth_rule}}";
    }

    public function attributeLabels()
    {
        return
        [
            'name'=>'规则名',
            'data'=>'序列化数据',
            'created_at'=>'创建时间',
            'updated_at'=>'更新时间',
        ];
    }
}