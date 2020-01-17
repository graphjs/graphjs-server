<?php

namespace GraphJS;

use Mailgun\Mailgun;
use Mailgun\Model\Message\SendResponse;
use Psr\Http\Message\ResponseInterface;
use function getenv;

/**
 * A wrapper for the MailGun SDK to make it compliant with SmtpInterface
 *
 */
class SmtpMailerMailGun implements SmtpMailerInterface
{

    /**
     * @var Mailgun
     */
    private $client;

    public function __construct()
    {
        $this->client = Mailgun::create(getenv("MAILGUN_KEY"));
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $body
     * @return SendResponse|ResponseInterface
     */
    public function sendMessage(string $from, string $to, string $subject, string $body)
    {
        return $this->client->messages()->send(getenv("MAILGUN_DOMAIN"), [
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'text' => $body
        ]);
    }

}
