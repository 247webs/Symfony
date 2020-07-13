<?php

namespace AppBundle\Controller;

use AppBundle\Representation\Companies;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/** @Route("/companies") */
class CompaniesController extends FOSRestController
{
    /**
     * @Rest\Get("", name="companies_get")
     *
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filter"
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
     *      name="limit",
     *      requirements="\d+",
     *      default="25",
     *      description="Max number of results"
     * )
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The pagination offset"
     * )
     *
     * @Rest\QueryParam(
     *      name="active",
     *      requirements="active|inactive|all",
     *      default="all",
     *      description="Return active, inactive or all records"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Retrieve Companies",
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
        $orderBy = $paramFetcher->get('order_by');
        $orderDirection = $paramFetcher->get('order_direction');
        if (empty($orderDirection) || strtoupper($orderDirection) != 'DESC') {
            $orderDirection = 'ASC';
        }
        $limit = (empty($paramFetcher->get('limit'))) ? 25 : $paramFetcher->get('limit');
        $page = (empty($paramFetcher->get('page'))) ? 1 : $paramFetcher->get('page');
        $active = strtolower($paramFetcher->get('active'));

        $repo = $this->getDoctrine()->getRepository('AppBundle:Company');
        $query = $repo->filter($filter, $orderBy, $orderDirection, $active);

        $companies =  new Companies($query, $repo->getCount(), $limit, $page);

        $response = [];
        $response['meta'] = $companies->meta;

        $data = [];
        foreach ($companies->data as $item) {
            $context = new SerializationContext();
            $context->setGroups("private");
            $data[] = json_decode($this->get('jms_serializer')->serialize($item, 'json', $context), true);
        }

        $response['companies'] = $data;


        return $this->view($response, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/anonymous", name="companies_anonymously_get")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filter"
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
     *      name="limit",
     *      requirements="\d+",
     *      default="25",
     *      description="Max number of results"
     * )
     * @Rest\QueryParam(
     *      name="offset",
     *      requirements="\d+",
     *      default="1",
     *      description="The pagination offset"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Retrieve Companies Anonymously",
     *      https="true",
     *      statusCodes={
     *         200 = "Returned when successful",
     *         401 = "Unauthorized",
     *         404 = "Returned when records are not found"
     *     }
     * )
     */
    public function getAnonymouslyAction(ParamFetcherInterface $paramFetcher)
    {
        $filter = $paramFetcher->get('filter');
        $orderBy = $paramFetcher->get('order_by');
        $orderDirection = $paramFetcher->get('order_direction');
        if (empty($orderDirection) || strtoupper($orderDirection) != 'DESC') {
            $orderDirection = 'ASC';
        }
        $limit = (empty($paramFetcher->get('limit'))) ? 25 : $paramFetcher->get('limit');
        $offset = (empty($paramFetcher->get('offset'))) ? 1 : $paramFetcher->get('offset');

        $repo = $this->getDoctrine()->getRepository('AppBundle:Company');
        $query = $repo->filterAnonymously($filter, $orderBy, $orderDirection);

        $companies = new Companies($query, $repo->getCount(), $limit, $offset);

        $response = [];
        $response['meta'] = $companies->meta;

        $data = [];
        foreach ($companies->data as $item) {
            $context = new SerializationContext();
            $context->setGroups("public");
            $data[] = json_decode($this->get('jms_serializer')->serialize($item, 'json', $context), true);
        }

        $response['companies'] = $data;


        return $this->view($response, Response::HTTP_OK);
    }
}
