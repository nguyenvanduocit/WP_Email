<?php
    /**
     * @date: 3/24/16
     */

namespace WP_Mail;

/**
 * Class AdminPage
 *
 * Plugin's admin page.
 *
 * @package WP_Mail
 */
class AdminPage extends \AEngine\Abstracts\AdminPage
{
    public function setup()
    {
        $this->args = ['page_title' => __('WP Mail')];
    }

    public function page_content()
    {
        echo $this->form_table([
                [
                    'label' => 'Mailgun API Key',
                    'type' => 'text',
                    'name' => 'mailgun_key',
                ],
            ]);
    }
}
