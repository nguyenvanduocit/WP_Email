<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail\API;

use WP_Error;
use WP_REST_Request;

class Inbound
{
    /**
     * handle inbound request.
     *
     * @param WP_REST_Request $request
     *
     * @return mixed|WP_Error
     */
    public function postInbound(WP_REST_Request $request)
    {
        global $WP_MAIL;
        $headers = $request->get_headers();
        foreach ($WP_MAIL->getEmailProviders() as $provider) {
            if ($provider->test($headers)) {
                $messageData = $provider->recive($request);

                return $this->insertMessage($messageData);
            }
        }

        return new WP_Error('unhandled_provider', __('Can not process your request', __WPM_DOMAIN__), ['status' => 406]);
    }

    /**
     * Insert new message to database as inbound posttype.
     *
     * @param $messageData
     *
     * @return mixed
     */
    protected function insertMessage($messageData)
    {
        $inbound_post = [
            'post_type' => 'inbound',
            'post_title' => wp_strip_all_tags($messageData['subject']),
            'post_content' => $messageData['html'],
            'post_status' => 'publish',
            'post_author' => 1,
        ];
        $postID = wp_insert_post($inbound_post);
        if (is_wp_error($postID)) {
            return $postID;
        }
        update_post_meta($postID, 'recipient', $messageData['recipient']);
        update_post_meta($postID, 'sender', $messageData['sender']);
        update_post_meta($postID, 'from', $messageData['from']);

        return $postID;
    }
}
