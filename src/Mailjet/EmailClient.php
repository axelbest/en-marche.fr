<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailer\AbstractEmailClient;
use AppBundle\Mailer\EmailClientInterface;
use AppBundle\Mailer\Exception\MailerException;
use GuzzleHttp\ClientInterface as Guzzle;
use GuzzleHttp\Exception\ClientException;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;

class EmailClient extends AbstractEmailClient implements EmailClientInterface
{
    private $logger;

    public function __construct(Guzzle $httpClient, string $publicKey, string $privateKey, Logger $logger)
    {
        parent::__construct($httpClient, $publicKey, $privateKey);
        $this->logger = $logger;
    }

    public function sendEmail(string $email): string
    {
        try {
            $response = $this->request('POST', 'send', ['body' => $email]);
        } catch (ClientException $clientException) {
            $this->logger->error('Client error', $clientException);

            return '';
        }

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to send email to recipients.');
        }

        return (string) $response->getBody();
    }
}
