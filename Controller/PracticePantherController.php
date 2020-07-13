<?php

namespace AppBundle\Controller;

use AppBundle\Entity\PracticePantherToken;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @Route("/practice-panther") */
class PracticePantherController extends FOSRestController
{
    /**
     * @Rest\Get(path="/token", name="practice_panther_token_get")
     *
     * @Doc\ApiDoc(
     *      section="Practice Panther",
     *      description="Get Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getToken()
    {
        $token = $this->getDoctrine()->getRepository(PracticePantherToken::class)->findOneBy([
            'user' => $this->getUser()->getId()
        ]);

        if (!$token) {
            throw new NotFoundHttpException('User does not have a Practice Panther token');
        }

        return $token;
    }

    /**
     * @Rest\Post(path="/token", name="practice_panther_token_post")
     *
     * @Rest\QueryParam(
     *      name="auth_code",
     *      nullable=false,
     *      description="Authorization Code"
     * )
     *
     * @Rest\QueryParam(
     *      name="redirect_uri",
     *      description="Redirect URI"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Practice Panther",
     *      description="Post Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function postToken(ParamFetcherInterface $paramFetcher)
    {
        $authCode = $paramFetcher->get('auth_code');
        $redirectUri = $paramFetcher->get('redirect_uri');

        if (empty($authCode)) {
            throw new BadRequestHttpException("Auth Code is required");
        }

        if (empty($redirectUri)) {
            throw new BadRequestHttpException("Redirect URI is required");
        }

        $token = $this->get('practice_panther_service')->generateToken($authCode, $redirectUri, $this->getUser());

        return $this->view(
            ['message' => $token ? 'success' : 'fail'],
            $token ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Rest\Delete(path="/token", name="practice_panther_token_delete")
     *
     * @Doc\ApiDoc(
     *      section="Practice Panther",
     *      description="Delete Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function deleteToken()
    {
        $tokens = $this->getDoctrine()->getRepository(PracticePantherToken::class)->findBy([
            'user' => $this->getUser()
        ]);

        if (count($tokens)) {
            foreach ($tokens as $token) {
                $this->getDoctrine()->getManager()->remove($token);
            }

            $this->getDoctrine()->getManager()->flush();
        }

        return $this->view([], Response::HTTP_NO_CONTENT);
    }
}
