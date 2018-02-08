<?php
/**
 * Category.php.
 * User: lvfk
 * Date: 2017/12/23 0023
 * Time: 16:23
 * Desc:
 */

namespace app\models;


use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\data\Pagination;
use yii\db\ActiveRecord;

class Category extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class'=>AttributeBehavior::className(),
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT=>['createtime']
                ],
                'value'=>function($event){
                    return time();
                }
            ],
            [//动态对表中字段的操作
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'adminid',
                'updatedByAttribute' => null,
                'value' => \Yii::$app->admin->id,
            ]

        ];
    }

    public static function tableName()
    {
        return '{{%category}}';
    }

    public function rules()
    {
        return [
            [['parentid'],'required','message'=>'上级分类不能为空','except'=>'rename'],
            [['title'],'required','message'=>'标题不能为空'],
            ['createtime','safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'cateid'=>'分类ID',
            'title'=>'分类名称',
            'parentid'=>'上级分类',
        ];
    }

    /**
     * @param $data
     * @return bool添加分类
     */
    public function add($data){
        if($this->load($data) && $this->validate() && $this->save()){
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return bool添加分类
     */
    public function mod($data){
        if($this->load($data) && $this->validate() && $this->update()){
            return true;
        }
        return false;
    }

    /**
     * 获取分类
     * @return array|ActiveRecord[]
     */
    public function getData(){
        $cates = self::find()->asArray()->all();
        return $cates;
    }

    /**
     * 获取分类等级树
     * @param $cates
     * @param $pid
     * @return array
     */
    public function getTree($cates, $pid){
        $tree = [];
        foreach ($cates as $cate){
            if($cate['parentid'] == $pid){
                $tree[] = $cate;
                $tree = array_merge($tree, $this->getTree($cates, $cate['cateid']));
            }
        }
        return $tree;
    }

    /**
     * 添加前缀
     * @param $data
     * @param string $p
     * @return array
     */
    public function setPrefix($data, $p='|----'){
        $tree = [];
        $num = 1;
        $prefix = [0=>1];
        while ($val = current($data)){
            $key = key($data);
            if($key > 0){
                if($data[$key-1]['parentid'] != $val['parentid']){
                    $num++;
                }
            }
            if(array_key_exists($val['parentid'], $prefix)){
                $num = $prefix[$val['parentid']];
            }
            $val['title'] = str_repeat($p, $num).$val['title'];
            $prefix[$val['parentid']] = $num;
            $tree[] = $val;
            next($data);
        }
        return $tree;
    }

    /**
     * 获取下拉列表数据
     * @return array
     */
    public function getOptions(){
        $cates = $this->getData();
        $tree = $this->getTree($cates, 0);
        $tree = $this->setPrefix($tree);
        //组装
        array_unshift($tree,['cateid'=>0,'title'=>'顶级分类']);
        return $tree;
    }

    /**
     * 获取列表数据
     * @return array
     */
    public function getTreeList(){
        $cates = $this->getData();
        $tree = $this->getTree($cates, 0);
        $tree = $this->setPrefix($tree);
        return $tree;
    }

    /**
     * @return array
     */
    public static function getMenu()
    {
        $top = self::find()->where('parentid = :pid', [":pid" => 0])->limit(11)->orderby('createtime asc')->asArray()->all();
        $data = [];
        foreach((array)$top as $k=>$cate) {
            $cate['children'] = self::find()->where("parentid = :pid", [":pid" => $cate['cateid']])->limit(10)->asArray()->all();
            $data[$k] = $cate;
        }
        return $data;
    }

    /**
     * 通过分类ID获取子类的ID
     * @param $pid
     * @return array
     */
    public static function getTreeCates($pid){
        $cates = self::find()->asArray()->all();
        $tree = [];
        foreach ($cates as $cate){
            if($cate['parentid'] == $pid){
                $tree[] = $cate;
                $tree = array_merge($tree, self::getTreeCates($cate['cateid']));
            }
        }
        return $tree;
    }

    /**
     * jsTree获取顶级分类
     */
    public function getPrimaryCate(){
        //获取顶级分类
        $data = self::find()->where("parentid = :pid",[":pid"=>0]);
        if(empty($data)){
            return [];
        }
        //分页获取
        $pager = new Pagination(['totalCount'=>$data->count(),'pageSize'=>10]);
        $data = $data->orderBy('createtime desc')->offset($pager->offset)->limit($pager->limit)->all();
        if(empty($data)){
            return [];
        }

        //封装数据
        $primary = [];
        foreach ($data as $cate){
            $primary[] = [
                'id' => $cate->cateid,
                'text' => $cate->title,
                'children' => $this->getChild($cate->cateid)
            ];
        }

        return ['data'=>$primary, 'pager' => $pager];
    }

    /**
     * 递归查询所有子类数据
     */
    private function getChild($pid){
        $data = self::find()->where("parentid = :pid",[":pid"=>$pid])->all();
        if(empty($data)){
            return [];
        }

        $children = [];
        foreach ($data as $child){
            $children[] = [
                'id' => $child->cateid,
                'text' => $child->title,
                'children' => $this->getChild($child->cateid)
            ];
        }
        return $children;
    }
}