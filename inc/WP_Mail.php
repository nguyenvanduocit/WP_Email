<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail;

use AEngine\Option;
use AEngine\PluginBase;
use WP_Mail\API\Inbound;
use WP_Mail\API\Webhook;

/**
 * Class WP_Mail
 *
 * Plugin main class.
 *
 * @package WP_Mail
 */
class WP_Mail extends PluginBase
{
    /** @var  \WP_Mail\Provider\Provider[] All email automation providers.*/
    protected $providers;
    /** @var Option AEngine\Option Theme options */
    protected $options;

    public function __construct()
    {
        $defaultOptions = [
            'mailgun_key' => 'your_key',
        ];
        $this->options = new Option('wpm_options', $defaultOptions, __WPM_FILE__);
    }

    public function run()
    {
        parent::run();

        if (is_admin()) {
            new AdminPage(__WPM_FILE__, $this->options);
        }
    }

    public function getPostTypeClasses()
    {
        return [
            '\WP_Mail\PostType\Inbound',
            '\WP_Mail\PostType\Outbound',
        ];
    }

    public function getAPIRoutes()
    {
        $inboundAPI = new Inbound();
        $webhookAPI = new Webhook();

        return [
            [
                'namespace' => 'wpmail/'.__WPM_API_VERSION__,
                'route' => '/inbound',
                'args' => [
                        'methods' => 'POST',
                        'callback' => [$inboundAPI, 'postInbound'],
                        'args' => [
                            'timestamp' => [
                                'required' => true,
                            ],
                            'token' => [
                                'required' => true,
                            ],
                            'signature' => [
                                'required' => true,
                            ],
                        ],
                ],
            ],
            [
                'namespace' => 'wpmail/'.__WPM_API_VERSION__,
                'route' => '/webhook',
                'args' => [
                        'methods' => 'POST',
                        'callback' => [$webhookAPI, 'postWebhook'],
                        'args' => [
                            'timestamp' => [
                                'required' => true,
                            ],
                            'token' => [
                                'required' => true,
                            ],
                            'signature' => [
                                'required' => true,
                            ],
                        ],
                ],
            ],
        ];
    }

    /**
     * Get all email automation service providers.
     *
     * @return Provider\Provider[]
     */
    public function getEmailProviders()
    {
        if (!$this->providers) {
            $providerClasses = [
                '\WP_Mail\Provider\Mailgun\Mailgun',
            ];
            foreach ($providerClasses as $class) {
                $this->providers[$class] = new $class();
            }
        }

        return $this->providers;
    }

    public function getDIR()
    {
        return __WPM_DIR__;
    }

    public function getFILE()
    {
        return __WPM_FILE__;
    }
}
