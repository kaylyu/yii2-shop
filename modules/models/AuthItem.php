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

class AuthItem extends ActiveRecord
{
    public static function  tableName()
    {
        return "{{%auth_item}}";
    }

    public function attributeLabels()
    {
        return
        [
            'name'=>'标识',
            'description'=>'名称',
            'rule_name'=>'规则名称',
            'created_at'=>'创建时间',
            'updated_at'=>'更新时间',
        ];
    }

    /**
     *获取可以待分配的角色或者权限
     * @param object $data 总的角色和权限数据
     * @param object $parent    分配父节点
     * @return array    返回可以待分配的角色数组或者权限数组
     */
    public static function getOptions($data, $parent){
        $out = [];
        foreach ($data as $obj){
            if(!empty($parent) && $parent->name != $obj->name && \Yii::$app->authManager->canAddChild($parent, $obj)){
                $out[$obj->name] = $obj->description;
            }

            if( is_null($parent)){
                $out[$obj->name] = $obj->description;
            }
        }
        return $out;
    }

    /**
     * 分配权限
     * @param array $children   指定分配权限数组
     * @param string $parentName   待分配父节点标识
     * @return bool 分配成功或者失败
     */
    public static function  addChild($children, $parentName){
        $auth = \Yii::$app->authManager;
        $itemObj = $auth->getRole($parentName);
        if(empty($itemObj)){
           return false;
        }

        $trans = \Yii::$app->db->beginTransaction();
        try{
            //先清除数据
            $auth->removeChildren($itemObj);
            //分配
            foreach ($children as $item){
                //判断下分配的是角色还是权限
                $obj = empty($auth->getRole($item)) ? $auth->getPermission($item) : $auth->getRole($item);
                //添加权限
                $auth->addChild($itemObj, $obj);
            }

            $trans->commit();
        }catch (Exception $e){
            $trans->rollBack();
            return false;
        }

        return true;
    }

    /**
     * 获取角色拥有的子角色和权限
     * @param string $name  角色名
     * @return array|bool   角色列表和权限列表
     */
    public static function getChildrenByName($name){
        if(empty($name)){
            return false;
        }

        $auth = \Yii::$app->authManager;
        $out = [];
        $out['roles'] = [];
        $out['permissions'] = [];

        $children = $auth->getChildren($name);
        if(empty($children)){
            return false;
        }
        foreach ($children as $child){
            if($child->type == 1){//角色
                $out['roles'][] = $child->name;
            }else{
                $out['permissions'][] = $child->name;
            }
        }

        return $out;
    }
}