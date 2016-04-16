<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail\PostType;

use AEngine\PostType\Base;

/**
 * Class Inbound
 *
 * Inbound posttype define
 *
 * @package WP_Mail\PostType
 */
class Inbound extends Base
{
    public function __construct()
    {
        $this->postType = 'inbound';
        $this->isPublic = false;
        $this->support = ['title', 'editor'];
        $this->meta_fields = [
            [
                'title' => __('Recipient', __WPM_DOMAIN__),
                'type' => 'text',
                'name' => 'recipient',
            ], [
                'title' => __('Sender', __WPM_DOMAIN__),
                'type' => 'text',
                'name' => 'sender',
            ], [
                'title' => __('From', __WPM_DOMAIN__),
                'type' => 'text',
                'name' => 'from',
            ],
        ];
    }
}
