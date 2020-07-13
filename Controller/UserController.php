<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Branch;
use AppBundle\Entity\CompanyAccessRequest;
use AppBundle\Entity\User;
use AppBundle\Enumeration\CompanyAccessRequestStatus;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use AppBundle\Repository\CompanyAccessRequestRepository;
use AppBundle\Representation\PublicOffers;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/user") */
class UserController extends FOSRestController
{
    /**
     * @Rest\Get(path="/open-access-requests", name="user_open_access_requests_get")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Get open user access requests",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="User access requests not found"
     *     }
     * )
     */
    public function getOpenAccessRequests()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var CompanyAccessRequestRepository $repo */
        $repo = $em->getRepository(CompanyAccessRequest::class);

        $openRequests = $repo->findBy([
            'accessRequestor' => $this->getUser()->getId(),
            'accessRequestStatus' => CompanyAccessRequestStatus::OPEN
        ]);

        if (!$openRequests) {
            throw new NotFoundHttpException();
        }

        return $openRequests;
    }

    /**
     * @Rest\Put(path="/open-access-requests/cancel", name="user_open_access_requests_cancel")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Cancel open user access requests",
     *      https="true",
     *      statusCodes={
     *         200="Success"
     *     }
     * )
     */
    public function cancelOpenAccessRequests()
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(CompanyAccessRequest::class);

        $openRequests = $repo->findBy([
            'accessRequestor' => $this->getUser()->getId(),
            'accessRequestStatus' => CompanyAccessRequestStatus::OPEN
        ]);

        foreach ($openRequests as $companyAccessRequest) {
            $companyAccessRequest->setAccessRequestStatus(CompanyAccessRequestStatus::DENIED);
            $em->persist($companyAccessRequest);
            $em->flush();
        }

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get(path="/{user}", name="user_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *     "user",
     *     options={
     *          "repository_method" = "findOneByIdSlugOrApiKey",
     *          "mapping": {"user": "user"},
     *          "map_method_signature" = true
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Get user",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     *
     * @return \FOS\RestBundle\View\View
     * @throws AccessDeniedHttpException
     */
    public function getAction(User $user)
    {
        $this->denyAccessUnlessGranted('view', $user);

        return $this->view($user, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(path="/{user}/offers", name="user_offers_get")
     *
     * @ParamConverter(
     *     "user",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="500",
     *      description="Max number of results"
     * )
     *
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The page"
     * )
     *
     * @Rest\QueryParam(
     *      name="videos_only",
     *      requirements="true|false",
     *      default="false",
     *      description="Only return offers with videos"
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Get user offers",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="User not found"
     *     }
     * )
     */
    public function getOffersAction(User $user, ParamFetcherInterface $paramFetcher)
    {
        ini_set('memory_limit', '1G'); // Boost the memory
        ini_set('max_execution_time', 0); // Allow unlimited execution time for companies only

        $page = $paramFetcher->get('page');
        if (empty($page)) {
            $page = 1;
        }

        $limit = $paramFetcher->get('limit');
        if (empty($limit)) {
            $limit = 500;
        }

        $videosOnly = !empty($paramFetcher->get('videos_only')) &&
            'true' == strtolower($paramFetcher->get('videos_only'))
            ? true : false;

        $offers = $this->get('user_service')->getUserOfferFeed($user, null, null, false, $videosOnly);

        if (!count($offers)) {
            throw new NotFoundHttpException(
                $user->getFirstName() . ' ' . $user->getLastName() . " does not have any offers to share yet"
            );
        }

        $statService = $this->get('statistic_service');

        return new PublicOffers(
            $statService->calculateAverageScoreByUser($user),
            $offers,
            $statService->countScorableOffersByUser($user),
            $page,
            $limit
        );
    }

    /**
     * @Rest\Get(path="/{user}/offers/summary", name="user_offers_summary_get")
     *
     * @ParamConverter(
     *     "user",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Get user offers summary",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="User not found"
     *     }
     * )
     */
    public function getOffersSummaryAction(User $user)
    {
        $response = new \StdClass;
        $statService = $this->get('statistic_service');


        $response->total_offers = $statService->countScorableOffersByUser($user);
        $response->average_rating = $statService->calculateAverageScoreByUser($user);

        return $this->view((array) $response, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(path="/branch/{branch}/users", name="user_branch_users_get")
     *
     * @ParamConverter(
     *     "branch",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Get All users of given branch",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="User access requests not found"
     *     }
     * )
     */
    public function getBranchUsers(Branch $branch)
    {
        $data = [];
        foreach ($branch->getUsers() as $item) {
            $context = new SerializationContext();
            $context->setGroups("private");
            $data[] = json_decode($this->get('jms_serializer')->serialize($item, 'json', $context), true);
        }

        return $this->view($data, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(path="/{id}", name="user_put")
     *
     * @ParamConverter(
     *      "user",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="user_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Modify a user",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data",
     *         404="User not found"
     *     }
     * )
     *
     * @param User                             $id
     * @param User                             $user
     * @param ConstraintViolationListInterface $violations
     * @param Request                          $request
     * @return \FOS\RestBundle\View\View
     * @throws ApiProblemException|AccessDeniedHttpException
     */
    public function putAction(
        User $id,
        User $user,
        ConstraintViolationListInterface $violations,
        Request $request
    ) {
        $userService = $this->get('user_service');
        $params = $request->request->all();
        $isAdmin = $this->isGranted('ROLE_BRANCH_ADMIN', $this->getUser());

        $this->denyAccessUnlessGranted('edit', $id);

        if (array_key_exists('plain_password', $params)) {
            $this->denyAccessUnlessGranted('edit_password', $id);
        }

        if (count($violations)) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
            throw new ApiProblemException($apiProblem);
        }

        $password = array_key_exists('plain_password', $params) ? $params['plain_password'] : null;

        $u = $userService->updateUser($id, $user, $isAdmin, $password);

        /** Manually handle serialization when groups are used (JMS Bug?) */
        $serializer = $this->get('serializer');
        $u = $serializer->serialize($u, 'json', SerializationContext::create()->setGroups(['private']));
        $view = $this->view(json_decode($u, true), Response::HTTP_OK);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post(path="", name="user_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "user",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="user_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Create a user",
     *      https="true",
     *      statusCodes={
     *         201="Created",
     *         400="Invalid data"
     *     }
     * )
     *
     * @param User                             $user
     * @param ConstraintViolationListInterface $violations
     * @throws ApiProblemException
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(User $user, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
            throw new ApiProblemException($apiProblem);
        }

        $userService = $this->get('user_service');
        $u = $userService->createUser($user, $userService->getIsAdmin($this->getUser()));

        return $this->view($u, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Post(path="/invite", name="user_invite")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @ParamConverter(
     *      "user",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="user_invite"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Invite a user",
     *      https="true",
     *      statusCodes={
     *         201="Created",
     *         400="Invalid data"
     *     }
     * )
     */
    public function inviteAction(User $user, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
            throw new ApiProblemException($apiProblem);
        }

        return $this->view(
            $this->get('user_service')->inviteUser($user, $this->getUser()),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Delete(path="/{id}", name="user_delete")
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Delete a user",
     *      https="true",
     *      statusCodes={
     *         204="User deleted",
     *         404="Not found"
     *     }
     * )
     *
     * @param User $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(User $id)
    {
        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Put(path="/{username}/hash", name="user_put_hashes")
     *
     * @ParamConverter(
     *      "user",
     *      converter="doctrine.orm"
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Create reset hashes for a user",
     *      https="true",
     *      statusCodes={
     *         204="User found, hashes created",
     *         400="Bad request",
     *         404="User not found"
     *     }
     * )
     *
     * @param User                  $user
     * @param Request               $request
     * @return \FOS\RestBundle\View\View
     */
    public function putHashesAction(User $user, Request $request)
    {
        $callback = $request->query->get('callback');
        $em = $this->getDoctrine()->getManager();

        $user->setSecurityHash($this->get('user_service')->getSecurityHash());
        $user->setSecurityHashExpiry(new \DateTime(date('Y-m-d H:i:s', strtotime('+24 hours'))));
        $em->flush();

        $callback .= '?h='.$user->getSecurityHash().'&u='.md5($user->getUsername()).'&i='.$user->getUsername();

        $this->get('mailer')->sendPasswordResetEmail($user, $callback);

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Put(path="/{username}/hash/{hash}", name="user_put_password")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Reset user password",
     *      https="true",
     *      statusCodes={
     *         200="Password successfully reset",
     *         400="Invalid password",
     *         404="User not found or hash expired"
     *     }
     * )
     *
     * @param string                           $username
     * @param string                           $hash
     * @param Request                          $request
     * @return \FOS\RestBundle\View\View
     * @throws BadRequestHttpException|ApiProblemException
     */
    public function putPasswordAction(
        $username,
        $hash,
        Request $request
    ) {
//        if (count($violations)) {
//            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
//            throw new ApiProblemException($apiProblem);
//        }

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:User');
        $u = $repo->findOneBySecurityHash($hash);

        if (null === $u) {
            throw new NotFoundHttpException(
                'Password reset link no longer valid. Please create a new password reset request.'
            );
        }

        if (md5($u->getUsername()) !== $username) {
            throw new BadRequestHttpException(
                'A security exception occurred. Please create a new password reset request.'
            );
        }

        $params = $request->request->all();

        $u->setPassword($this->get('security.password_encoder')->encodePassword($u, $params['plain_password']));
        $u->setSecurityHash(null);
        $u->setSecurityHashExpiry(null);
        $em->flush();

        return $this->view(['message' => 'Password successfully reset'], Response::HTTP_OK);
    }

    /**
     * @Rest\Post(path="/{user}/share", name="user_share_post")
     *
     * @ParamConverter(
     *     "user",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="User",
     *      description="Record a share statistic for a user",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="User not found"
     *     }
     * )
     */
    public function userPostShareAction(User $user)
    {
        $stat = $this->get('statistic_service')->recordShare($user);

        return $this->view($stat, Response::HTTP_CREATED);
    }
}
