<?php
/**
 * RbacController.php.
 * User: lvfk
 * Date: 2018/1/26 0026
 * Time: 10:05
 * Desc:
 */

namespace app\commands;


use yii\console\Controller;
use yii\db\Exception;

class RbacController extends Controller
{
    /**
     * 批量导入权限节点
     */
    public function actionInit(){
        $trans = \Yii::$app->db->beginTransaction();

        try{
            //获取待添加的权限
            $dir = dirname(dirname(__FILE__)).'/modules/controllers';
            $controllers = glob($dir.'/*');
            $permissions = [];
            foreach ($controllers as $controller) {
                $content = file_get_contents($controller);
                preg_match('/class ([a-zA-Z]+)Controller/', $content, $match);
                $cName = $match[1];
                //添加控制器下所有权限
                $permissions[] = strtolower($cName).'/*';
                //获取action的控制权限
                preg_match_all('/public function action([a-zA-Z_]+)/', $content, $matchs);
                foreach ($matchs[1] as $aName){
                    $permissions[] = strtolower($cName).'/'.strtolower($aName);
                }
            }
            //添加权限
            $auth = \Yii::$app->authManager;
            foreach ($permissions as $permission){
                if(!$auth->getPermission($permission)){
                    $obj = $auth->createPermission($permission);
                    $obj->description = $permission;
                    $auth->add($obj);
                }
            }
            $trans->commit();
            echo "import success \n";
        }catch ( Exception $e){
            $trans->rollBack();
            echo "import failed \n";
        }
    }
}