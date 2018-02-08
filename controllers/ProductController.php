<?php
/**
 * ProductController.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 14:30
 * Desc:
 */
namespace app\controllers;

use app\models\Category;
use app\models\Product;
use app\models\ProductSearch;
use yii\data\Pagination;
use yii\filters\AccessControl;

class ProductController extends CommonController {
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' =>[
                    [
                        'allow' => true,
                        'roles' => ['@','?']
                    ]
                ]
            ]
        ];
    }
    public $layout = 'layout_second';
    public function actionIndex(){
        $cid = \Yii::$app->request->get("cateid");
        //通过CID查找子分类信息
        $cates = Category::getTreeCates($cid);
        $cids = array_merge(array_column($cates, 'cateid'),array($cid));
        $model = Product::find()->andWhere(['in','cateid',$cids])->andWhere("ison='1'");
        $all = $model->asArray()->all();

        //获取分类名字
        $cateName = Category::findOne($cid)->title;

        $count = $model->count();
        $pageSize = \Yii::$app->params['pageSize']['frontproduct'];
        $pager = new Pagination(['totalCount' => $count, 'pageSize' => $pageSize]);
        $all = $model->offset($pager->offset)->limit($pager->limit)->asArray()->all();

        $tui = $model->andWhere("istui='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        $hot = $model->andWhere("ishot='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        $sale = $model->andWhere("issale='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        return $this->render("index", [
            'sale' => $sale,
            'tui' => $tui,
            'hot' => $hot,
            'all' => $all,
            'pager' => $pager,
            'count' => $count,
            'cateName' => $cateName
        ]);
    }

    public function actionDetail(){
        $productid = \Yii::$app->request->get("productid");
        $product = Product::find()->where('productid = :id', [':id' => $productid])->asArray()->one();
        $data['all'] = Product::find()->where('ison = "1"')->orderby('createtime desc')->limit(7)->all();
        return $this->render("detail", ['product' => $product, 'data' => $data]);
    }

    /**
     * @param $keyword
     * @return string
     */
    public function actionSearch($keyword){
        $keyword = htmlentities($keyword);
        $pageSize = \Yii::$app->params['pageSize']['frontproduct'];
        try{
            //利用elasticsearch进行检索
            $searchModel = ProductSearch::find()->query([
                'multi_match'=>[
                    'query'=>$keyword,
                    'fields' => ['title','descr']
                ]
            ]);
            $count = $searchModel->count();
            $pager = new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);

            $data = $searchModel->highlight([
                "pre_tags"=>["<em>"],
                "post_tags"=>["</em>"],
                "fields"=>[
                    "title" => new \stdClass(),
                    "descr" => new \stdClass(),
                ]
            ])->offset($pager->offset)->limit($pager->limit)->all();
        }catch (\Exception $e){
            $data = [];
            $count = 0;
            $pager = new Pagination(['totalCount'=>$count,'pageSize'=>$pageSize]);

        }


        $products = [];
        if(count($data) > 0){
            foreach ($data as $item){
                $product = Product::findOne($item->productid);
                $product->title = !empty($item->highlight['title'][0]) ? $item->highlight['title'][0] : $product->title;
                $product->descr = !empty($item->highlight['descr'][0]) ? $item->highlight['descr'][0] : $product->descr;
                $products[] = $product;
            }
        }

        $model = Product::find();
        $tui = $model->andWhere("istui='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        $hot = $model->andWhere("ishot='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        $sale = $model->andWhere("issale='1'")->orderby('createtime desc')->limit(5)->asArray()->all();
        return $this->render("index", [
            'sale' => $sale,
            'tui' => $tui,
            'hot' => $hot,
            'all' => $products,
            'pager' => $pager,
            'count' => $count,
            'cateName' => '搜索结果'
        ]);
    }
}