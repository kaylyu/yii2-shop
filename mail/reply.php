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
    尊敬的用户,您好:
</p>

<p>您邮箱注册 <b>商城</b> 的账号信息如下：</p>

<p>账号：<?php echo $username?></p>
<p>账号密码：<?php echo $userpass?></p>
<p>电子邮箱：<?php echo $useremail?></p>

<p>请点击下面链接进行最后一步验证，验证成功后，可以使用注册的邮箱进行登录 <b>商城</b>：</p>

<?php
    $url = Yii::$app->urlManager->createAbsoluteUrl([
        'member/regmail',
        'username'=>$username,
        'userpass'=>$userpass,
        'useremail'=>$useremail,
        'time'=>$time,
        'token'=>$token
    ]);
?>

<p><a href="<?=$url;?>"><?=$url;?></a></p>

<p>该链接5分钟内有效，请勿</p>

<p>该邮件为系统自动发送，请勿回复！</p>
