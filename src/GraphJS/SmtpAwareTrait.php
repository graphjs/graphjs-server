<?php

use GraphJS\SmtpMailerInterface;
use GraphJS\SmtpMailerMailGun;
use Psr\Http\Message\ResponseInterface;

/**
 * Trait MailerAwareController
 *
 * @author Olivier Maurel
 */
trait SmtpAwareTrait {

    /** @var SmtpMailerInterface */
    private $smtpClient = null;

    public function smtpSend(string $from, string $to, string $subject, string $body): ResponseInterface
    {
        if(is_null($this->smtpClient)) {
            // change to another Smtp client if needed here
            $this->smtpClient = new SmtpMailerMailGun();
        }

        return $this->smtpClient->sendMessage($from, $to, $subject, $body);
    }

}
