<?php
/**
 * Message.php.
 * User: lvfk
 * Date: 2018/2/3 0003
 * Time: 16:40
 * Desc:
 */

namespace lvfk\mailerqueue;


use yii\base\InvalidConfigException;

class Message extends \yii\swiftmailer\Message
{
    /**
     * 存储邮件到redis队列中
     * @return mixed
     * @throws InvalidConfigException
     */
    public function queue(){
        //检测redis
        $redis = \Yii::$app->redis;
        if(empty($redis)){
            throw new InvalidConfigException('redis not found in config');
        }

        //检测mailer
        $mailer = \Yii::$app->mailer;
        if(empty($redis) || !$redis->select($mailer->db) || empty($mailer->key)){
            throw new InvalidConfigException('mailer not found in config');
        }
        if(empty($mailer->key)){
            throw new InvalidConfigException('parameter key not found in mailer config');
        }
        if(!$redis->select($mailer->db)){
            throw new InvalidConfigException('parameter db not found in mailer config');
        }


        //发送
        $message = [];
        $message['from'] = array_keys($this->from);
        $message['to'] = array_keys($this->getTo());
        $message['cc'] = !empty($this->getCc())?array_keys($this->getCc()):[];
        $message['bcc'] = !empty($this->getBcc())?array_keys($this->getBcc()):[];
        $message['reply_to'] = !empty($this->getReplyTo())?array_keys($this->getReplyTo()):[];
        $message['charset'] = $this->getCharset();
        $message['subject'] = $this->getSubject();
        //正文处理
        $parts = $this->getSwiftMessage()->getChildren();
        if(!is_array($parts) || count($parts) == 0){
            $parts = [$this->getSwiftMessage()];
        }
        foreach ($parts as $part){
            if(!$parts instanceof \Swift_Mime_Attachment){//判断是否为附件
                switch ($part->getContentType()){//判断内容格式
                    case "text/html":
                        $message['html_body'] = $part->getBody();
                        break;
                    case "text/plain":
                        $message['text_body'] = $part->getBody();
                        break;
                }
            }
            if(!empty($message['charset'])){
                $message['charset'] = $part->getCharset();
            }
        }

        return $redis->rpush($mailer->key, json_encode($message));
    }
}