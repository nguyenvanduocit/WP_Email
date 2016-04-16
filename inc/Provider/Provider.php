<?php
/**
 * @date: 3/22/16
 */

namespace WP_Mail\Provider;

/**
 * Class Provider
 *
 * Email automation provider abstract class.
 *
 * @package WP_Mail\Provider
 */
abstract class Provider
{
    /**
     * Send message.
     *
     * @param $message
     *
     * @return mixed
     */
    abstract public function send($message);

    /**
     * Process recived message.
     *
     * @param $request
     *
     * @return mixed
     */
    abstract public function recive($request);

    /**
     * Process recived webhook request.
     *
     * @param $request
     *
     * @return array|WP_Error
     */
    abstract public function webhook($request);

    /**
     * Test if this provider can process the message.
     *
     * @param $origin
     *
     * @return mixed
     */
    abstract public function test($origin);

    /**
     * Get the title of provider.
     *
     * @return mixed
     */
    abstract public function getTitle();
}
