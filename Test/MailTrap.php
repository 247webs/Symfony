<?php

namespace AppBundle\Test;

use GuzzleHttp\Client as GuzzleClient;

class MailTrap
{
    const API_KEY = '82336d587f9f2f1111d009eb5ec4cfdc';
    const MAILBOX_ID = 123169;
    const BASE_URI = 'https://mailtrap.io/api/v1';

    /**
     * @var GuzzleClient
     */
    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new GuzzleClient([
            'headers' => [
                'Authorization' => 'Token token=' . static::API_KEY,
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getLatestMessage()
    {
        $res = $this->httpClient->get(static::BASE_URI . '/inboxes/' . static::MAILBOX_ID . '/messages');

        $message = json_decode($res->getBody(), true)[0];

        return [
            'subject' => $message['subject'],
            'from_email' => $message['from_email'],
            'from_name' => $message['from_name'],
            'to_email' => $message['to_email'],
            'to_name' => $message['to_name'],
            'html_body' => $message['html_body'],
        ];
    }

    /**
     * @return int
     */
    public function countUnreadMessages()
    {
        $res = $this->httpClient->get(static::BASE_URI . '/inboxes/' . static::MAILBOX_ID);

        $response = json_decode($res->getBody(), true);

        return $response['emails_unread_count'];
    }

    public function clearInbox()
    {
        $this->httpClient->patch(static::BASE_URI . '/inboxes/' . static::MAILBOX_ID . '/clean');
    }
}

