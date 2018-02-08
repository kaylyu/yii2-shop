<?php
use yii\grid\GridView;
use yii\helpers\Html;
$this->title = '规则列表';
$this->params['breadcrumbs'][] = ['label'=>'规则管理', 'url' => ['/admin/rbac/rules']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerCssFile('admin/css/compiled/user-list.css', ['position'=>\yii\web\View::POS_HEAD]);
?>

<div class="container-fluid">
    <div id="pad-wrapper" class="users-list">
        <div class="row-fluid header">
            <h3>规则列表</h3>
            <div class="span10 pull-right">
                <a href="<?php echo yii\helpers\Url::to(['rbac/createrule']) ?>" class="btn-flat success pull-right">
                    <span>&#43;</span>
                    添加新规则
                </a>
            </div>
        </div>
        <?=GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' =>[
                    [
                        'class'=>'yii\grid\SerialColumn',
                    ],
                    [
                        'attribute'=>'name',
                        'format'=>'text',
                        'filter'=>Html::activeTextInput($searchModel, 'name', ['class'=>'', 'placeholder'=>'请输入规则名过滤'])
                    ],
                    [
                        'attribute'=>'created_at',
                        'format'=>'datetime',
                        'filter'=>Html::activeTextInput($searchModel, 'created_at', ['class'=>'', 'placeholder'=>'请输入创建时间过滤'])
                    ],
                    [
                        'attribute'=>'updated_at',
                        'format'=>'datetime',
                        'filter'=>Html::activeTextInput($searchModel, 'updated_at', ['class'=>'', 'placeholder'=>'请输入更新时间过滤'])
                    ],

                    [
                        'class'=>'yii\grid\ActionColumn',
                        'header'=>'操作',
                        'template'=>'{assign} {update} {delete}',
                        'buttons'=>[
                            'assign' => function($url, $model, $key){
                                return Html::a('分配权限',
                                    ['assignitem','name'=>$model['name']],
                                    ['title'=>'分配权限','class'=>'']);
                            },
                            'update' => function($url, $model, $key){
                                return Html::a('更新',
                                    ['updateitem', 'name'=> $model['name']],
                                    ['title' => '更新']);
                            },
                            'delete' => function($url, $model, $key){
                                return Html::a('删除',
                                    ['deleteitem', 'name'=>$model['name']],
                                    ['title'=>'删除', 'data'=>['confirm'=>'是否确认删除?']]);
                            }
                        ]
                    ],
                ],
                'layout' => "\n{items}\n{summary}<div class='pagination pull-right'>{pager}</div>",
                'emptyText'=>'当前没有记录',
                'emptyTextOptions'=>['style'=>'color:red;font-weight:bold'],
                'showOnEmpty'=>false
            ]);?>
    </div>
</div>
