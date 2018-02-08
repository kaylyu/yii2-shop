<?php
/**
 * seekpass.php.
 * User: Administrator
 * Date: 2017/9/28 0028
 * Time: 11:55
 * Desc:
 */
?>

<p>
    尊敬的<?=$adminuser;?>,您好:
</p>

<p>您的找回密码链接如下：</p>

<?php
    $url = Yii::$app->urlManager->createAbsoluteUrl([
        'admin/manage/mailchangepass',
        'timestamp' => $time,
        'adminuser' => $adminuser,
        'token' => $token
    ]);
?>

<p><a href="<?=$url;?>"><?=$url;?></a></p>

<p>该链接5分钟内有效，请勿</p>

<p>该邮件为系统自动发送，请勿回复！</p>
