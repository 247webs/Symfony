<?php

namespace AppBundle\Controller;

use AppBundle\Representation\Businesses;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/** @Route("/business") */
class BusinessController extends FOSRestController
{
    /**
     * @Rest\Get(path="/", name="search_profile")
     *
     * @Doc\ApiDoc(
     *      section="Business Profile",
     *      description="Get the business profiles",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */

    /**
     * @Rest\Get("", name="search_profile")
     *
     * @Rest\QueryParam(
     *      name="industry",
     *      nullable=true,
     *      description="industry"
     * )
     * @Rest\QueryParam(
     *      name="rating",
     *      nullable=true,
     *      description="Rating"
     * )
     * @Rest\QueryParam(
     *      name="searchText",
     *      nullable=true,
     *      description="searchText"
     * )
     * @Rest\QueryParam(
     *      name="latitude",
     *      nullable=true,
     *      description="latitude"
     * )
     * @Rest\QueryParam(
     *      name="longitude",
     *      nullable=true,
     *      description="longitude"
     * )
     * @Rest\QueryParam(
     *      name="order_by",
     *      description="Order by"
     * )
     * @Rest\QueryParam(
     *      name="order_direction",
     *      default="ASC",
     *      description="Order direction (ascending or descending)"
     * )
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filter"
     * )
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="100",
     *      description="Max number of results"
     * )
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The pagination page"
     * )
     *
     */
    public function searchAction(ParamFetcherInterface $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        
        /* Receiving business keywords */
        $industry = $paramFetcher->get('industry');
        $rating = ($paramFetcher->get('rating')) ? $paramFetcher->get('rating') : null;
        $searchText = $paramFetcher->get('searchText');
        $latitude = $paramFetcher->get('latitude');
        $longitude = $paramFetcher->get('longitude');

        /* Receiving required page number and limit */
        $limit = ($paramFetcher->get('limit')) ? $paramFetcher->get('limit') : 25;
        $page = ($paramFetcher->get('page')) ? $paramFetcher->get('page') : 1;

        /* Call business service to get the businesses (Company, Branch, User Profiles) based on given keywords */
        $result = $this->get('business_service')->searchBusiness($industry, $rating, $searchText, $latitude, $longitude);

        /* Handover businesses array to Representation class to make it representable and applying pagination */
        $branches = new Businesses($result, count($result), $limit, $page);

        $response = [];
        $response['meta'] = $branches->meta;
        $response['data'] = $branches->getData();

        return $this->view($response, Response::HTTP_OK);
    }
}