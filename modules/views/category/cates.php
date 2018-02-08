
<?php
$this->registerCssFile('admin/css/compiled/user-list.css',['position'=>\yii\web\View::POS_HEAD]);
?>

<!-- main container -->
<div class="container-fluid">
    <div id="pad-wrapper" class="users-list">
        <div class="row-fluid header">
            <h3>分类列表</h3>
            <div class="span10 pull-right">
                <a href="<?php echo yii\helpers\Url::to(['category/add']) ?>" class="btn-flat success pull-right">
                    <span>&#43;</span>
                    添加新分类
                </a>
            </div>
        </div>

            <?php
            if (Yii::$app->session->hasFlash('info')) {
                echo Yii::$app->session->getFlash('info');
            }
            ?>
        <!-- Users table -->
        <div class="row-fluid table">
            <?= \yiidreamteam\jstree\JsTree::widget([
                'containerOptions' => [
                    'class' => 'data-tree',
                ],
                'jsOptions' => [
                    'core' => [
                        'check_callback' => true,
                        'multiple' => false,
                        'data' => [
                            'url' => \yii\helpers\Url::to(['category/tree','page'=>$page,'per-page'=>$perpage]),
                        ],
                        'themes' => [
                            'stripes' => true,
                            'variant' => 'large'
                        ]
                    ],
                    'plugins' => [
                        'contextmenu','dnd','search','state','types','wholerow'
                    ]
                ]
            ]) ?>
        </div>
        <div class="pagination pull-right">
            <?php echo yii\widgets\LinkPager::widget([
                'pagination' => $pager,
                'prevPageLabel' => '&#8249;',
                'nextPageLabel' => '&#8250;',
                ]); ?>
        </div>
        <!-- end users table -->
    </div>
</div>
<!-- end main container -->

<?php
//注册监听rename事件JS
$renameUrl = \yii\helpers\Url::to(['category/rename']);
$deleteUrl = \yii\helpers\Url::to(['category/delete']);
//拼装CSRF
$csrfvar = Yii::$app->request->csrfParam;
$csrfval = Yii::$app->request->csrfToken;
$js = <<<JS
$('#w0').on('rename_node.jstree', function(e, data) {
    var newtext = data.text;
    var old = data.old;
    var id = data.node.id;
    var postData = {
        "$csrfvar" : "$csrfval",
        'new' : newtext,
        'old' : old,
        'id' : id,
    };
    $.post('$renameUrl', postData, function(data) {
        if(data.code != 0){
            alert('修改失败');
            window.location.reload();
        }
    });
}).on('delete_node.jstree', function(e, data) {
    var id = data.node.id;
    $.get('$deleteUrl', {id: id},function(data) {
        if(data.code != 0){
            alert('删除失败，'+data.message);
            window.location.reload();
        }
    });
});


JS;

$this->registerJs($js);

?>