<?php
/**
 * auth.php.
 * User: Administrator
 * Date: 2017/9/26 0026
 * Time: 17:23
 * Desc:
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
$this->title = '登录';
?>

    <!-- ========================================= MAIN ========================================= -->
    <main id="authentication" class="inner-bottom-md">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <?php
                        if (Yii::$app->session->hasFlash('reg_info')) {
                            echo Yii::$app->session->getFlash('reg_info');
                        }else{
                            echo '非法请求';
                        }
                    ?>
                </div>
            </div><!-- /.row -->
        </div><!-- /.container -->
    </main><!-- /.authentication -->
    <!-- ========================================= MAIN : END ========================================= -->

    <script>

        $(document).ready(function () {
            $('#login_qq').click(function () {
//                window.location.href = '<?//=\yii\helpers\Url::to(['member/qqlogin'])?>//';
                window.open('<?= \yii\helpers\Url::to(['member/qqlogin'])?>', '','Width=' + 640 +', height=' + 480 + ',depended=yes,resizable=no, scrollbars=no, status=no, toolbar=no, menubar=no, location=no, left=0, top=0')

            });
        });

    </script>