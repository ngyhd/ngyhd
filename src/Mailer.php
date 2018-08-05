<?php
/**
 * Created by PhpStorm.
 * User: ngyhd
 * Date: 2018/8/5
 * Time: 1:46
 */
namespace ngyhd\ngyhd;

use yii\base\InvalidConfigException;
use yii\web\ServerErrorHttpException;

class Mailer extends \yii\swiftmailer\Mailer
{
    public $messageClass = 'ngyhd\ngyhd\Message';

    public $key = 'mails';

    public $db = '1';

    public function process()
    {
        $redis = \Yii::$app->redis;
        if (empty($redis)) {
            throw new InvalidConfigException('redis not found in config');
        }
        if ($redis->select($this->db) && $messages = $redis->lrange($this->key, 0, -1)){
            $messageObj = new Message();
            foreach ($messages as $message){
                $message = json_decode($message, true);
                if (empty($message) || !$this->setMessage($messageObj, $message)) {
                    throw new ServerErrorHttpException('message error');
                }
                if ($messageObj->send()){
                    $redis->lrem($this->key, -1, json_encode($message));
                }
            }
        }
        return true;
    }

    public function setMessage($messageObj, $message)
    {
        if (empty($messageObj)) {
            return false;
        }

        if (!empty($message['from']) && !empty($message['to'])) {
            $messageObj->setFrom($message['form'])->setTo($message['to']);
            if (!empty($message['cc'])) {
                $messageObj->setCc($message['cc']);
            }
            if (!empty($message['bcc'])) {
                $messageObj->setCc($message['bcc']);
            }
            if (!empty($message['reply_to'])) {
                $messageObj->setCc($message['reply_to']);
            }
            if (!empty($message['charset'])) {
                $messageObj->setCc($message['charset']);
            }
            if (!empty($message['subject'])) {
                $messageObj->setCc($message['subject']);
            }
            if (!empty($message['html_body'])) {
                $messageObj->setCc($message['html_body']);
            }
            if (!empty($message['text_body'])) {
                $messageObj->setCc($message['text_body']);
            }
        }
    }
}