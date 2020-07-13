<?php

namespace AppBundle\Test;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractApiTest extends AbstractTest
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @param string $key
     */
    protected function setApiKeyForRequest($key)
    {
        $this->apiKey = $key;
    }

    protected function setAdminApiKeyForRequest()
    {
        $this->apiKey = $this->generateApiToken(static::ADMIN_USERNAME, static::ADMIN_PASSWORD);
    }

    /**
     * @param string $route
     * @param array $params
     * @param string $method
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function makeRequest($route, array $params = [], $method = 'GET')
    {
        $client = new Client();

        if ($method == 'PUT' || $method == "DELETE") {
            $uri = $this->generateUrl($route, ['id' => $params['id']]);
        } else {
            $uri = $this->generateUrl($route, $method == 'GET' ? $params : []);
        }

        $options = [];

        if (null !== $this->apiKey) {
            $options['headers'] = [
                'Authorization' => 'Bearer '. $this->apiKey,
            ];
        }

        if ($method == 'POST' || $method == 'PUT') {
            $options['json'] = $params;
        }

        $options = array_merge($options, $this->defaultClientOptions);

        return $client->request($method, $uri, $options)->getBody();
    }

    /**
     * @param $username
     * @param $password
     * @return string
     */
    protected function generateApiToken($username, $password)
    {
        $client = new Client();

        $uri = $this->generateUrl('api_login_check');

        $options = array_merge($this->defaultClientOptions, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                '_username' => $username,
                '_password' => $password,
            ],
        ]);

        $response = $client->request('POST', $uri, $options)->getBody();

        $response = json_decode($response, true);

        return $response['token'];
    }
}