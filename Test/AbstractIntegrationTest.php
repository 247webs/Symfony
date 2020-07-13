<?php

namespace AppBundle\Test;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractIntegrationTest extends AbstractTest
{
    /**
     * @param string $route
     * @param array $params
     * @param string $method
     * @return ResponseInterface
     */
    protected function makeRequest($route, array $params = [], $method = 'GET')
    {
        $client = new Client();

        $uri = $this->generateUrl($route, $method == 'GET' ? $params : []);

        $options = [];

        if ($method == 'POST') {
            $options['form_params'] = $params;
        }

        $options = array_merge($options, $this->defaultClientOptions);

        return $client->request($method, $uri, $options);
    }
}