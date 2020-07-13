<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ClioToken;
use AppBundle\Entity\ClioUser;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @Route("/clio") */
class ClioController extends FOSRestController
{
    /**
     * @Rest\Post(path="/authorize", name="clio_authorize_post")
     *
     * @ParamConverter(
     *     "branch",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Rest\QueryParam(
     *      name="auth_code",
     *      default=null,
     *      description="Auth code"
     * )
     *
     * @Rest\QueryParam(
     *      name="redirect_uri",
     *      default=null,
     *      description="Redirect Uri"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Clio",
     *      description="Authorize and Get Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function authorize(ParamFetcherInterface $paramFetcher)
    {
        $auth_code = $paramFetcher->get('auth_code');
        $redirect_uri = $paramFetcher->get('redirect_uri');
        $company = $this->getUser()->getBranch()->getCompany();
        $token = $this->get('clio_service')->generateToken($auth_code, $redirect_uri, $company);
        return $this->view(
            ['message' => $token ? 'success' : 'fail'],
            $token ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Rest\Get(path="/token", name="clio_token_get")
     *
     * @Doc\ApiDoc(
     *      section="Clio",
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
        $token = $this->getDoctrine()->getRepository(ClioToken::class)->findOneBy([
            'company' => $this->getUser()->getBranch()->getCompany()->getId()
        ]);
        if (!$token) {
            throw new NotFoundHttpException('User does not have a Clio token');
        }
        $this->denyAccessUnlessGranted('view', $token);
        return $token;
    }

    /**
     * @Rest\Get(path="/{token}/users", name="clio_get_users")
     *
     * @Doc\ApiDoc(
     *     section="Clio",
     *     description="Get Doctors",
     *     https="true",
     *     statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getUsersAction(ClioToken $token)
    {
        $this->denyAccessUnlessGranted('view', $token);

        return $this->get('clio_service')->getUsers($token);
    }

    /**
     * @Rest\Get("/{token}/mapped-users", name="clio_mapped_users_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Clio",
     *      description="Get a list of users mapped to a Clio account",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getMappedUsersAction(ClioToken $token)
    {
        $this->denyAccessUnlessGranted('view', $token);

        return $this->get('clio_service')->getMappedUsers($token);
    }

    /**
     * @Rest\Post("/{token}/users", name="clio_users_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Clio",
     *      description="Map Clio users to the eOffer users",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postUsersAction(ClioToken $token, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $token);

        $data = json_decode($request->getContent(), true);
        $valid = [];
        $invalid = [];

        foreach ($data as $user) {
            $p = $this->get('clio_service')->createUserFromArray($token, $user);

            $errors = $this->get('validator')->validate($p, null, ["clio_user_post"]);

            (count($errors)) ?
                $invalid[] = $p->getUser()->getFirstName() . ' ' . $p->getUser()->getLastName() :
                $valid[] = $p;
        }
        $results = $this->get('clio_service')->addUsers($token, $valid);

        // handle any other fails
        $errors = [];
        if (count($results->notAdded)) {
            $errors = array_merge($invalid, $results->notAdded);
        }

        return $this->view(
            ['users_created' => $results->added, 'invalid_users' => $errors],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Delete(path="/{token}", name="clio_token_delete")
     *
     * @Doc\ApiDoc(
     *      section="Clio",
     *      description="Delete Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function deleteTokenAction(ClioToken $token)
    {
        $this->denyAccessUnlessGranted('edit', $token);

        // Delete users of the given clio account
        $this->deleteUsersAction($token);

        // Delete clio account
        $this->getDoctrine()->getManager()->remove($token);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param ClioToken $token
     * @return void
     */
    private function deleteUsersAction(ClioToken $token)
    {
        $users = $this->getDoctrine()->getRepository(ClioUser::class)->findBy([
            'clioToken' => $token
        ]);

        if (count($users)) {
            foreach ($users as $user) {
                $this->getDoctrine()->getManager()->remove($user);
            }

            $this->getDoctrine()->getManager()->flush();
        }
    }
}
