<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail\PostType;

use AEngine\PostType\Base;

/**
 * Class Outbound
 *
 * Outbound posttype define.
 *
 * @package WP_Mail\PostType
 */
class Outbound extends Base
{

    public function __construct()
    {
        global $WP_MAIL;

        $this->postType = 'outbound';
        $this->hierarchical = true;
        $this->isPublic = false;
        $this->support = ['title','editor', 'comments'];
        $providerOptions = [];
        foreach ($WP_MAIL->getEmailProviders() as $classPath => $provider){
            $classPath = wp_slash($classPath);
            $providerOptions[$classPath] = $provider->getTitle();
        }
        $this->meta_fields = [
            [
                'title'=>'To',
                'type' => 'text',
                'name' => 'to',
                'desc' =>'Use commas to separate multiple recipients',
            ],[
                'title'=>'Cc',
                'type' => 'text',
                'name' => 'cc',
            ],[
                'title'=>'Bcc',
                'type' => 'text',
                'name' => 'bcc',
            ],[
                'title'=>'From',
                'type' => 'text',
                'name' => 'from',
            ],[
                'title'=>'Provider',
                'type' => 'select',
                'value' =>$providerOptions,
                'name' => 'provider',
            ]
        ];
        add_action(  'save_post',  [$this, "onPostSaved"], 100, 3);
    }


    /**
     * @param $new_status
     * @param $old_status
     * @param $post
     *
     * @internal param $ID
     */
    public function onPostSaved($post_ID, $post, $update){

        if ( wp_is_post_revision( $post->ID ) || $this->postType != $post->post_type || $post->post_status != 'publish' ) {
            return;
        }
        if ( ! isset( $_POST['action'] ) || $_POST['action'] != 'editpost' ) {
            return;
        }
        if ( ! isset( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_ID ) {
            return;
        }
        global $WP_MAIL;
        $providerClass  = get_post_meta($post->ID,'provider', true);
        $providers = $WP_MAIL->getEmailProviders();
        if(array_key_exists($providerClass, $providers)){
            $message = [
                'to' =>get_post_meta($post->ID, 'to', true),
                'cc' =>get_post_meta($post->ID, 'cc', true),
                'bcc' =>get_post_meta($post->ID, 'bcc', true),
                'from' =>get_post_meta($post->ID, 'from', true),
                'subject' =>$post->post_title,
                'html' =>$post->post_content
            ];

            $attachments = get_posts([
                'post_type' => 'attachment',
                'posts_per_page' => -1,
                'post_parent' => $post->ID
            ]);
            if(count($attachments) > 0 ){
                $message['attachments'] = [];
                foreach ( $attachments as $attachment ) {
                    if ( $file = get_post_meta( $attachment->ID, '_wp_attached_file', true) ) {
                        // Get upload directory.
                        if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) {
                            $message['attachments'][] = $uploads['basedir'] . "/$file";
                        }
                    }
                }
            }

            /** @var \stdClass|\WP_Error $result */
            $result = $providers[$providerClass]->send($message);
            if(is_wp_error($result)){
                $data = [
                    'comment_post_ID' => $post->ID,
                    'comment_author' => "Mailgun - Error",
                    'comment_author_url' => 'http://senviet.org',
                    'comment_content' => sprintf("%s<br>%s", $result->get_error_code(), $result->get_error_message())
                ];
                wp_insert_comment($data);
            }else{
                $messageId = trim($result->http_response_body->id, "<>");
                $data = [
                    'comment_post_ID' => $post->ID,
                    'comment_author' => "Mailgun",
                    'comment_author_url' => 'http://senviet.org',
                    'comment_content' => sprintf("ID: %s<br>Response: %s", $messageId, $result->http_response_body->message)
                ];
                $commentId = wp_insert_comment($data);
                update_comment_meta($commentId, 'message_id', $messageId);
            }
        }
    }
}
