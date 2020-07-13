<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RecaptchaController
 * @package AppBundle\Controller
 *
 * @Route("/recaptcha")
 */
class RecaptchaController extends FOSRestController
{
    /**
     * @Rest\Post(path="/verify", name="recaptcha_verify")
     */
    public function verifyAction(Request $request)
    {
        $body = json_decode($request->getContent());

        if (!$body || !array_key_exists('response', $body)) {
            return $this->view(['Invalid'], Response::HTTP_BAD_REQUEST);
        }

        $client = new Guzzler();

        try {
            $response = $client->request(
                'POST',
                $this->getParameter('recaptcha_verification_url') .
                    '?secret=' . $this->getParameter('recaptcha_secret') . '&response=' . $body->response,
                []
            );

            $result = json_decode($response->getBody());
        } catch (RequestException $e) {
            return $this->view(['Invalid'], Response::HTTP_BAD_REQUEST);
        }

        return (true === $result->success) ?
            $this->view(['Valid'], Response::HTTP_OK) :
            $this->view(['Invalid'], Response::HTTP_BAD_REQUEST);
    }
}
