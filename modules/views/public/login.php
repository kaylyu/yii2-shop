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
$this->title = '商城后台管理-登录';
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
    <a class="brand" href="<?=Url::to(['public/login']); ?>"></a>
    <?php $form = ActiveForm::begin([
            'fieldConfig' => [
                'template' => '{error}{input}'//去掉输入框上面提示名，如用户名
            ]
    ]);?>
        <div class="span4 box">
            <div class="content-wrap">
                <h6>商城后台管理</h6>
                <div class="form-group field-admin-adminuser">
                    <?=$form->field($model, 'adminuser')->textInput(['class' => 'span12','placeholder' => "管理员账号"]); ?>
                </div>
                <div class="form-group field-admin-adminpass">
                    <?=$form->field($model, 'adminpass')->passwordInput(['class' => 'span12','placeholder' => "管理员密码"]); ?>
                </div>
                <a href="<?=Url::to(['public/seekpassword']); ?>" class="forgot">忘记密码?</a>
                <div class="form-group field-remember-me">
                    <?=$form->field($model, 'rememberMe')->checkbox([
                        'id'=>'remember-me',
                        'template' => '<div class="remember">{input}<label for="remember-me">记住我</label></div></div>'
                    ]); ?>
                </div>
                <?= Html::submitButton('登录', ['class' => 'btn-glow primary login']) ?>
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
