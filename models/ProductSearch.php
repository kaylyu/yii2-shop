<?php
/**
 * ProductSearch.php.
 * User: lvfk
 * Date: 2018/2/3 0003
 * Time: 11:55
 * Desc:
 */

namespace app\models;

use yii\elasticsearch\ActiveRecord;

/**
 * 利用elasticsearch
 * @package app\models
 */
class ProductSearch extends ActiveRecord
{
    public function attributes()
    {
        return [
          'productid','title','descr'
        ];
    }

    public static function index()
    {
        return 'yii2_shop';//创建ES的索引
    }

    public static function type()
    {
        return 'products'; //索引类型为products
    }
}