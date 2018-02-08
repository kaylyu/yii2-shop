<?php
/**
 * seekpassword.php.
 * User: Administrator
 * Date: 2017/9/28 0028
 * Time: 10:38
 * Desc:
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use app\assets\AdminLoginAsset;

AdminLoginAsset::register($this);
$this->title = '商城后台管理 - 找回密码';
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
<?php $this->beginBody()?>
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
                    <?=$form->field($model, 'adminuser')->textInput(['class' => 'span12', 'placeholder' => '管理员账号']);?>
                <div class="form-group field-admin-adminemail">
                    <?=$form->field($model, 'adminemail')->textInput(['class' => 'span12', 'placeholder' => '管理员邮箱']);?>
                <a href="<?=Url::to(['public/login'])?>" class="forgot">返回登录</a>
                    <?=Html::submitButton('找回密码', ['class' => 'btn-glow primary login']);?>
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

$this->endBody();
?>
</body>

</html>
<?php $this->endPage()?>
