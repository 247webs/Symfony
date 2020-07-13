<?php

namespace AppBundle\Services;

use GuzzleHttp\Client AS Guzzler;
use Symfony\Component\HttpFoundation\Response;

class GoogleMapService
{
    const DISTANCE_MATRIX_API_URL = 'https://maps.googleapis.com/maps/api/distancematrix/json';

    /** @var string $apiKey */
    private $apiKey;

    /**
     * GoogleMyBusinessService constructor.
     * @param string $apiKey
     */
    public function __construct(
        string $apiKey
    ) {
        $this->apiKey = $apiKey;
    }

    /**
     * @param array $origins
     * @param array $destinations
     * @return array
     */
    public function getDistanceMatrix($origins, $destinations)
    {
        $client = new Guzzler;
        $endpoint = $this::DISTANCE_MATRIX_API_URL .'?' .
            'key=' . $this->apiKey .
            '&origins=' . implode('|', $origins) .
            '&destinations=' . implode('|', $destinations);
        try {
            $response = $client->get($endpoint, []);
            return json_decode($response->getBody());
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }
}