<?php
/**
 * @date: 3/24/16
 */

namespace WP_Mail\API;

use WP_Error;
use WP_REST_Request;

/**
 * Class Webhook
 *
 * Handle all webhook request.
 *
 * @package WP_Mail\API
 */
class Webhook
{
    /**
     * handle for webhook request.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error
     */
    public function postWebhook(WP_REST_Request $request)
    {
        global $WP_MAIL;
        $headers = $request->get_headers();
        foreach ($WP_MAIL->getEmailProviders() as $provider) {
            if ($provider->test($headers)) {
                $event = $provider->webhook($request);

                switch ($event['event']) {
                    case 'delivered':
                        $args = [
                            'meta_key' => 'message_id',
                            'meta_value' => $event['id'],
                        ];
                        $comments = get_comments($args);
                        if (count($comments) > 0) {
                            $comment = $comments[0];
                            $data = [
                                'comment_post_ID' => $comment->comment_post_ID,
                                'comment_author' => 'Mailgun',
                                'comment_author_url' => 'http://senviet.org',
                                'comment_content' => sprintf('ID: %s<br>Status: delivered', $event['id']),
                            ];
                            $commentId = wp_insert_comment($data);
                            update_comment_meta($commentId, 'message_id', $event['id']);

                            return $commentId;
                        } else {
                            return new WP_Error('provider_id', __('This message id is not exist on database', __WPM_DOMAIN__), ['status' => 406]);
                        }
                        break;
	                default:
		                return new WP_Error('unaccept_event', __('This event is not acceptec', __WPM_DOMAIN__), ['status' => 406]);
		                break;
                }
            }
        }

        return new WP_Error('provider_invalid', __('Can not process your request', __WPM_DOMAIN__), ['status' => 406]);
    }
}
