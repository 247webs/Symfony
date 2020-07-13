<?php

namespace AppBundle\Controller;

use AppBundle\Representation\OfferResponses;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @Route("/offer-responses") */
class OfferResponsesController extends FOSRestController
{
    /**
     * @Rest\Get("", name="offer_responses_get")
     *
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filter"
     * )
     *
     * @Rest\QueryParam(
     *     name="user",
     *     nullable=true,
     *     description="Filter by User ID"
     * )
     *
     * @Rest\QueryParam(
     *      name="order_by",
     *      description="Order by"
     * )
     *
     * @Rest\QueryParam(
     *      name="order_direction",
     *      default="ASC",
     *      description="Order direction (ascending or descending)"
     * )
     *
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="100",
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
     *     name="disable_user_filter",
     *     default="false"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Offer Response",
     *      description="Retrieve Offer Responses",
     *      https="true",
     *      statusCodes={
     *         200 = "Returned when successful",
     *         401 = "Unauthorized",
     *         404 = "Returned when records are not found"
     *     }
     * )
     */
    public function getAction(ParamFetcherInterface $paramFetcher)
    {
        $filter = $paramFetcher->get('filter');
        $user = $paramFetcher->get('user');
        $orderBy = $paramFetcher->get('order_by');
        $orderDirection = $paramFetcher->get('order_direction');
        if (empty($orderDirection) || strtoupper($orderDirection) != 'DESC') {
            $orderDirection = 'ASC';
        }
        $limit = (empty($paramFetcher->get('limit'))) ? 100 : $paramFetcher->get('limit');
        $page = (empty($paramFetcher->get('page'))) ? 1 : $paramFetcher->get('page');

        $disableUserFilter = $paramFetcher->get('disable_user_filter');

        $dm = $this->get('doctrine_mongodb')->getManager();
        $offerResponseRepo = $dm->getRepository('AppBundle:OfferResponse');

        /**
         * eOffer Administrators may search against all offers by disabling the user filter
         */
        if ("true" == $disableUserFilter &&
            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
        ) {
            $query = $offerResponseRepo->filter(
                $filter,
                $orderBy,
                $orderDirection
            );

            return new OfferResponses(
                $query,
                $offerResponseRepo->getCount(),
                $limit,
                $page
            );
        }

        $userService = $this->get('user_service');

        /**
         * If user parameter is supplied, restrict access by ensuring the user has
         * company or branch admin rights to the user supplied in the param, or otherwise
         * the authenticated user is asking for his/her own records.
         */
        if (!empty($user)) {
            $authChecker = $this->get('security.authorization_checker');

            // Determine the logged in user's get role
            $keyRole = 'user';

            if ($authChecker->isGranted('ROLE_BRANCH_ADMIN')) {
                $keyRole = 'branchAdmin';
            }

            if ($authChecker->isGranted('ROLE_COMPANY_ADMIN')) {
                $keyRole = 'companyAdmin';
            }

            // The user we're being asked to filter on
            $userEntity = $this->getDoctrine()->getRepository('AppBundle:User')->find($user);

            // If the user entity is empty, return nothing
            if (!$userEntity) {
                return [];
            }

            switch ($keyRole) {
                case 'companyAdmin':
                    if (!$userService->getIsUsersCompanyAdmin($userEntity, $this->getUser())) {
                        throw new AccessDeniedHttpException("You are not permitted to access this resource");
                    }
                    break;
                case 'branchAdmin':
                    if (!$userService->getIsUsersBranchAdmin($userEntity, $this->getUser())) {
                        throw new AccessDeniedHttpException("You are not permitted to access this resource");
                    }
                    break;
                default:
                    if ($userEntity->getId() !== $this->getUser()->getId()) {
                        throw new AccessDeniedHttpException("You are not permitted to access this resource");
                    }
            }
        }


        /**
         * Get an array of user ids for the authenticated user.  Company admins will return all users for a company,
         * branch admins all users for a branch, and "regular" users an array of 1 containing their own user id.
         */
        $users = (isset($userEntity)) ? [$userEntity->getId()] : $userService->getUserIdsByUserRole($this->getUser());

        /**
         * Get an array of offer request ids for a group of users.
         */
        $offerRequests = $this->get('offer_request_service')->getOfferRequestsByUsers($users);

        /**
         * If there are no requests, return empty response; In this method, we don't want to
         * expose any offer responses that don't belong to the logged in user.
         */
        if (!count($offerRequests)) {
            return [];
        }

        /**
         * Find offer responses
         */
        $query = $offerResponseRepo->filter(
            $filter,
            $orderBy,
            $orderDirection,
            $offerRequests
        );

        /**
         * Return an offer responses representation
         */
        return new OfferResponses(
            $query,
            $offerResponseRepo->getCount(null, $offerRequests),
            $limit,
            $page
        );
    }

    /**
     * @Rest\Get("/consumer", name="customer_offer_responses_get")
     *
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filters sets separated by | (pipe) and delineated by ~(tilde)"
     * )
     *
     * @Rest\QueryParam(
     *     name="recipient_email",
     *     nullable=false,
     *     description="Filter by recipient e-mail"
     * )
     *
     * @Rest\QueryParam(
     *      name="order_by",
     *      description="Order by"
     * )
     *
     * @Rest\QueryParam(
     *      name="order_direction",
     *      default="ASC",
     *      description="Order direction (ascending or descending)"
     * )
     *
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="100",
     *      description="Max number of results"
     * )
     *
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The pagination offset"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Offer Request",
     *      description="Retrieve Offer Requests",
     *      https="true",
     *      statusCodes={
     *         200 = "Returned when successful",
     *         401 = "Unauthorized",
     *         404 = "Returned when records are not found"
     *     }
     * )
     */
    public function getCustomerOffersAction(ParamFetcherInterface $paramFetcher)
    {
        $filter = $paramFetcher->get('filter');
        $recipientEmail = $paramFetcher->get('recipient_email');
        $orderBy = $paramFetcher->get('order_by');
        $orderDirection = $paramFetcher->get('order_direction');
        if (empty($orderDirection) || strtoupper($orderDirection) != 'DESC') {
            $orderDirection = 'ASC';
        }
        $limit = (empty($paramFetcher->get('limit'))) ? 100 : $paramFetcher->get('limit');
        $page = (empty($paramFetcher->get('page'))) ? 1 : $paramFetcher->get('page');

        /**
         * Get an array of offer request ids for the given recipientEmail
         */
        $offerRequests = $this->get('offer_request_service')->getOfferRequestsByRecipient($recipientEmail);

        /**
         * Find offer responses
         */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $offerResponseRepo = $dm->getRepository('AppBundle:OfferResponse');
        $query = $offerResponseRepo->filter(
            $filter,
            $orderBy,
            $orderDirection,
            $offerRequests
        );

        /**
         * Return an offer responses representation
         */
        return new OfferResponses(
            $query,
            $offerResponseRepo->getCount(null, $offerRequests),
            $limit,
            $page
        );
    }
}
