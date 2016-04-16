<?php

/*
Plugin Name: WP Mail
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: A brief description of the Plugin.
Version: 1.0
Author: duoc
Author URI: http://URI_Of_The_Plugin_Author
License: A "Slug" license name e.g. GPL2
*/
define('__WPM_FILE__', __FILE__);
define('__WPM_DIR__', __DIR__);
define('__WPM_VERSION__', '1.0.0');
define('__WPM_API_VERSION__', 'v1');
define('__WPM_DOMAIN__', 'wpm');

require_once __WPM_DIR__.'/vendor/autoload.php';
use WP_Mail\WP_Mail;

global /** @var WP_Mail $WP_MAIL */ $WP_MAIL;
$WP_MAIL = new WP_Mail();
$WP_MAIL->run();
