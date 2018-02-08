<?php
/**
 * login.php.
 * User: Administrator
 * Date: 2017/9/27 0027
 * Time: 15:02
 * Desc:
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AdminLoginAsset;

AdminLoginAsset::register($this);
$this->title = '商城后台管理-修改密码';
?>

<?php $this->beginPage()?>
<!DOCTYPE html>
<html class="login-bg">
<head>
    <title><?php echo Html::encode($this->title);?></title>
    <?php
    $this->registerMetaTag(['name'=>'viewport','content'=>"width=device-width, initial-scale=1.0"]);
    $this->registerMetaTag(['http-equiv'=>'Content-Type','content'=>"text/html; charset=utf-8"]);
    ?>
    <?php $this->head()?>
</head>
<body>
<div class="row-fluid login-wrapper">
    <a class="brand" href="index.html"></a>
    <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'template' => '{error}{input}'//去掉输入框上面提示名，如用户名
            ]
    ]);?>
        <div class="span4 box">
            <div class="content-wrap">
                <h6>商城后台管理</h6>
                <?php if(Yii::$app->session->hasFlash('info')){
                    echo Yii::$app->session->getFlash('info');
                }?>
                <div class="form-group field-admin-adminuser">
                    <?=$form->field($model, 'adminuser')->hiddenInput(['class' => 'span12','placeholder' => "管理员账号"]); ?>
                </div>
                <div class="form-group field-admin-adminpass">
                    <?=$form->field($model, 'adminpass')->passwordInput(['class' => 'span12','placeholder' => "新密码"]); ?>
                </div>
                <div class="form-group field-admin-adminpass">
                    <?=$form->field($model, 'repass')->passwordInput(['class' => 'span12','placeholder' => "确认密码"]); ?>
                </div>
                <a href="<?=Url::to(['public/login'])?>" class="forgot">返回登录</a>
                <?= Html::submitButton('提交', ['class' => 'btn-glow primary login']) ?>
        </div>
    <?php ActiveForm::end();?>
</div>

<?php
$js = <<<JS
// bg switcher
var btns = $(".bg-switch .bg");
btns.click(function(e) {
e.preventDefault();
btns.removeClass("active");
$(this).addClass("active");
var bg = $(this).data("img");

$("html").css("background-image", "url('img/bgs/" + bg + "')");
});
JS;
$this->registerJs($js);

?>
<?php $this->endBody()?>
</body>

</html>
<?php $this->endPage()?>
