<?php
/**
 * Created by PhpStorm.
 * User: ngyhd
 * Date: 2018/8/5
 * Time: 1:12
 */

namespace ngyhd\ngyhd;

use yii\base\InvalidConfigException;

class Message extends \yii\swiftmailer\Message
{
        public function save()
        {
            $redis = \Yii::$app->redis;
            if(empty($redis))
            {
                throw new InvalidConfigException('redis not found in config');
            }

            $mailer = \Yii::$app->mailer;
            if (empty($mailer) || $redis->select($mailer->db))
            {
                throw new InvalidConfigException('do not defined');
            }

            $message = [];
            $message['form'] = array_keys($this->form);
            $message['to'] = array_keys($this->getTo());
            $message['cc'] = array_keys($this->getCc());
            $message['bcc'] = array_keys($this->getBcc());
            $message['reply_to'] = array_keys($this->getReplyTo());
            $message['charset'] = array_keys($this->getCharset());
            $message['subject'] = array_keys($this->getSubject());
            $parts = $this->getSwiftMessage()->getChildren();
            if (!is_array($parts) || !sizeof($parts)){
                $parts = [$this->getSwiftMessage()];
            }
            foreach ($parts as $part){
                if (!$part instanceof  \Swift_Mime_Attachment){
                    switch ($part->getContentType()){
                        case 'text/html':
                            $message['html_body'] = $part->getBody();
                            break;
                        case 'text/plain':
                            $message['text_body'] = $part->getBody();
                            break;
                    }
                    if (!$message['charset']){
                        $message['charset'] = $part->getCharset();
                    }
                }
            }

            return $redis->rpush($mailer->key, json_encode($message));
        }
}