<?php
/**
 * Mailer.php.
 * User: lvfk
 * Date: 2018/2/3 0003
 * Time: 17:06
 * Desc: send mail
 */

namespace lvfk\mailerqueue;


use yii\base\InvalidConfigException;
use yii\web\ServerErrorHttpException;

class Mailer extends \yii\swiftmailer\Mailer
{
    //指定到我们自定义的Message类
    public $messageClass = 'lvfk\mailerqueue\Message';

    public $db = '0';//默认为redis第0个数据库

    public $key = 'mails';//默认为redis的存储队列名字

    /**
     * 设置数据
     * @param $messageObj
     * @param $message
     * @return bool
     */
    private function setMessage($messageObj, $message){
        if (empty($messageObj)) {
            return false;
        }
        if (!empty($message['from']) && !empty($message['to'])) {
            $messageObj->setFrom($message['from'])->setTo($message['to']);
            if (!empty($message['cc'])) {
                $messageObj->setCc($message['cc']);
            }
            if (!empty($message['bcc'])) {
                $messageObj->setBcc($message['bcc']);
            }
            if (!empty($message['reply_to'])) {
                $messageObj->setReplyTo($message['reply_to']);
            }
            if (!empty($message['charset'])) {
                $messageObj->setCharset($message['charset']);
            }
            if (!empty($message['subject'])) {
                $messageObj->setSubject($message['subject']);
            }
            if (!empty($message['html_body'])) {
                $messageObj->setHtmlBody($message['html_body']);
            }
            if (!empty($message['text_body'])) {
                $messageObj->setTextBody($message['text_body']);
            }
            return $messageObj;
        }
        return false;
    }

    /**
     * 发邮件
     */
    public function process(){
        //检测redis
        $redis = \Yii::$app->redis;
        if(empty($redis)){
            throw new InvalidConfigException('redis not found in config');
        }
        //获取邮件列表,并发送
        if($redis->select($this->db) && $messages = $redis->lrange($this->key, 0, -1)){
            $messageObj = new Message();
            foreach ($messages as $message){
                $message = json_decode($message, true);
                if(empty($message) || !$this->setMessage($messageObj, $message)){
                    throw new ServerErrorHttpException('message error');
                }
                if($messageObj->send()){//发送成功之后，删除队列中数据
                    $redis->lrem($this->key, -1, json_encode($message));
                }
            }
        }

        return true;
    }
}
