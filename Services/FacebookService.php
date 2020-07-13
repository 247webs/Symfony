<?php

namespace AppBundle\Services;

use AppBundle\Factories\BroadcasterFactory;
use AppBundle\Document\Sharing\Broadcaster\FacebookBroadcaster;
use Doctrine\ODM\MongoDB\DocumentManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;

class FacebookService
{
    /** @var DocumentManager $dm */
    private $dm;

    /** @var string $facebookClientId */
    private $facebookClientId;

    /** @var string $facebookClientSecret */
    private $facebookClientSecret;

    /** @var string $facebookOpenGraphUrl */
    private $facebookOpenGraphUrl = 'https://graph.facebook.com/';

    /** @var string $defaultGraphVersion */
    private $defaultGraphVersion = 'v4.0';

    /**
     * FacebookService constructor.
     * @param DocumentManager $dm
     * @param string $facebookClientId
     * @param string $facebookClientSecret
     */
    public function __construct(DocumentManager $dm, string $facebookClientId, string $facebookClientSecret)
    {
        $this->dm = $dm;
        $this->facebookClientId = $facebookClientId;
        $this->facebookClientSecret = $facebookClientSecret;
    }

    /**
     * @param string $accessToken
     * @return bool|\Facebook\GraphNodes\GraphUser
     */
    public function getUser(string $accessToken)
    {
        $fb = $this->getClient();

        try {
            $response = $fb->get('/me?fields=id,name', $accessToken);
            return $response->getGraphUser();
        } catch (FacebookResponseException $e) {
            return false;
        } catch (FacebookSDKException $e) {
            return false;
        }
    }

    /**
     * @param string $accessToken
     * @param array $fields
     * @return array|bool
     */
    public function getUserAccounts(string $accessToken, array $fields = [])
    {
        $fb = $this->getClient();
        $uri = '/me/accounts?limit=500';

        if (!empty($fields)) {
            $uri .= '&fields=';

            foreach ($fields as $field) {
                $uri .= $field . ',';
            }

            $uri = rtrim($uri, ',');
        }

        try {
            $response = $fb->get($uri, $accessToken);
            return $response->getDecodedBody();
        } catch (FacebookResponseException $e) {
            return false;
        } catch (FacebookSDKException $e) {
            return false;
        }
    }

    /**
     * @param string $accessToken
     * @param string $pageId
     * @param array $content
     * @param string $sharingType
     * @return bool|\Facebook\GraphNodes\GraphNode
     */
    public function postAsPage(string $accessToken, string $pageId, array $content, string $sharingType = null)
    {
        if (!$this->getPostContentValid($content)) {
            return false;
        }

        $suffix = ($sharingType && "video" === $sharingType) ? '/videos' : '/feed';
        $uri = '/' . $pageId . $suffix;

        return $this->post($uri, $content, $accessToken);
    }

    /**
     * @param string $accessToken
     * @param array $content
     * @param string $sharingType
     * @return bool|\Facebook\GraphNodes\GraphNode
     */
    public function postAsUser(string $accessToken, array $content, string $sharingType = null)
    {
        if (!$this->getPostContentValid($content)) {
            return false;
        }

        $suffix = ($sharingType && "video" === $sharingType) ? '/videos' : '/feed';
        $uri = '/me' . $suffix;

        return $this->post($uri, $content, $accessToken);
    }

    /**
     * @param FacebookBroadcaster $broadcaster
     * @param string $accessToken
     * @return bool
     */
    public function updateToken(FacebookBroadcaster $broadcaster, string $accessToken)
    {
        $tokenDetails = $this->getUserTokenDetails($accessToken);
        try {
            $tokenDetailsData = $tokenDetails->data;
            if ($tokenDetailsData->is_valid) {
                $expiryDate = date("Y-m-d h:i:s", $tokenDetailsData->data_access_expires_at);
                /** Add the token data to the broadcaster */
                $broadcaster = BroadcasterFactory::update(
                    $broadcaster,
                    null,
                    $expiryDate,
                    null,
                    null
                );
                $this->dm->persist($broadcaster);
                $this->dm->flush();
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $uri
     * @param array $content
     * @param string $accessToken
     * @return bool|\Facebook\GraphNodes\GraphNode
     */
    private function post(string $uri, array $content, string $accessToken)
    {
        $fb = $this->getClient();

        try {
            $response = $fb->post($uri, $content, $accessToken);
            return $response->getGraphNode();
        } catch (FacebookResponseException $e) {
            return false;
        } catch (FacebookSDKException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param array $content
     * @return bool
     */
    private function getPostContentValid(array $content)
    {
        return (
            (array_key_exists('link', $content) && null != $content['link']) ||
            (array_key_exists('file_url', $content) && null != $content['file_url'])
        );
    }

    /**
     * @return Facebook
     */
    private function getClient()
    {
        return new Facebook([
            'app_id' => $this->facebookClientId,
            'app_secret' => $this->facebookClientSecret,
            'default_graph_version' => $this->defaultGraphVersion,
        ]);
    }

    /**
     * @param string $inputToken
     * @return array|bool
     */
    private function getUserTokenDetails(string $inputToken)
    {
        $client = new Guzzler;

        $endpoint = $this->facebookOpenGraphUrl . $this->defaultGraphVersion . '/debug_token?' .
            'input_token=' . $inputToken .
            '&access_token=' . $this->facebookClientId . '|' . $this->facebookClientSecret;

        try {
            $response = $client->get($endpoint, []);
            return json_decode($response->getBody());
        } catch (RequestException $e) {
            return false;
        }
    }
}
