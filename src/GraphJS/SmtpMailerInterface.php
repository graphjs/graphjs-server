<?php

namespace GraphJS;

use Psr\Http\Message\ResponseInterface;

/**
 * Abstraction to allow the smtp service to be interchangeable in the controller
 *
 */
interface  SmtpMailerInterface {

    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return ResponseInterface
     */
    public function sendMessage(string $from, string $to, string $subject, string $body);

}