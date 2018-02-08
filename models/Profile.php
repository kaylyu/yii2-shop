<?php
/**
 * Profile.php.
 * User: Administrator
 * Date: 2017/9/29 0029
 * Time: 16:16
 * Desc:
 */

namespace app\models;


use yii\db\ActiveRecord;

class Profile   extends  ActiveRecord
{
   public static function tableName()
    {
        return "{{%profile}}";
    }
}