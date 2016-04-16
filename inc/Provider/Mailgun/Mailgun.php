<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail\Provider\Mailgun;

use WP_Error;
use WP_Mail\Provider\Provider;

/**
 * Class Mailgun
 *
 * Mailgun provider.
 *
 * @package WP_Mail\Provider\Mailgun
 */
class Mailgun extends Provider
{
    /**
         * @param $message
         *
         * @return \stdClass|WP_Error
         */
        public function send($message)
        {
            $mailgun = new \Mailgun\Mailgun(ae_get_option('mailgun_key'));
            $domain = '6iro.com';
            try {
                $data = [
                    'from' => $message['from'],
                    'to' => $message['to'],
                    'subject' => $message['subject'],
                    'html' => $message['html'],
                ];
                if (isset($message['cc']) && !empty($message['cc'])) {
                    $data['cc'] = $message['cc'];
                }
                if (isset($message['bcc']) && !empty($message['bcc'])) {
                    $data['bcc'] = $message['bcc'];
                }

                $files = [];
                if (isset($message['attachments']) && !empty($message['attachments'])) {
                    $files['attachment'] = $message['attachments'];
                };
                $result = $mailgun->sendMessage($domain, $data, $files);

                return $result;
            } catch (\Exception $e) {
                return new WP_Error('send_error', $e->getMessage());
            }
        }

        /**
         * Process recived webhook request.
         * TODO Process attachments.
         *
         * @param $request
         *
         * @return array|WP_Error
         */
        public function recive($request)
        {
            /*
             * Verify if this email come from your account
             */
            if (!$this->verifyToken($request['timestamp'], $request['token'], $request['signature'])) {
                return new WP_Error('auth_invalid', __('Invalid token', __WPM_DOMAIN__), ['status' => 406]);
            }

            $messageData = [
                'recipient' => $request['recipient'],
                'sender' => $request['sender'],
                'from' => $request['from'],
                'subject' => $request['subject'],
                'text' => $request['body-plain'],
                'html' => $request['body-html'],
            ];

            return $messageData;
        }

        /**
         * Verify message token.
         *
         * @param $timestamp
         * @param $token
         * @param $signature
         *
         * @return bool
         */
        public function verifyToken($timestamp, $token, $signature)
        {
            return hash_hmac('sha256', $timestamp.$token, ae_get_option('mailgun_key')) === $signature;
        }

        /**
         * Test of this provider can progess this message.
         *
         * @param $headers
         *
         * @return bool
         */
        public function test($headers)
        {
            return isset($headers['user_agent']) && strpos($headers['user_agent'], 'mailgun') !== false;
        }

    public function getTitle()
    {
        return 'Mailgun';
    }

        /**
         * Process Webhook.
         *
         * @param $request
         *
         * @return array|WP_Error
         */
        public function webhook($request)
        {
            /*
                 * Verify if this email come from your account
                 */
            if (!WP_DEBUG && !$this->verifyToken($request['timestamp'], $request['token'],
                    $request['signature'])
            ) {
                return new WP_Error('auth_invalid', __('Invalid token', __WPM_DOMAIN__),
                    ['status' => 406]); // 406 mean NOT Acceptable, mailgun will not retry to send message
            }

            return [
                'event' => $request['event'],
                'recipient' => $request['recipient'],
                'domain' => $request['domain'],
                'headers' => $request['message-headers'],
                'id' => trim($request['Message-Id'], '<>'),
            ];
        }
}
