<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EventScheduler\EventScheduleSendSurvey;
use AppBundle\Representation\EventSchedules;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/** @Route("/event-schedules") */
class EventSchedulesController extends FOSRestController
{
    /**
     * @Rest\Get("/send-survey", name="event_schedules_send_survey_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Rest\QueryParam(
     *      name="filter",
     *      nullable=true,
     *      description="Filter"
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
     *      default="25",
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
     *      name="active",
     *      requirements="active|inactive|all",
     *      default="all",
     *      description="Return active, inactive or all records"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Event Schedule",
     *      description="Retrieve a list of event schedules for sending surveys",
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
        // Filtering, pagination and order controls
        $filter = $paramFetcher->get('filter');
        $orderBy = $paramFetcher->get('order_by');
        $orderDirection = $paramFetcher->get('order_direction');
        if (empty($orderDirection) || strtoupper($orderDirection) != 'DESC') {
            $orderDirection = 'ASC';
        }
        $limit = (empty($paramFetcher->get('limit'))) ? 25 : $paramFetcher->get('limit');
        $page = (empty($paramFetcher->get('page'))) ? 1 : $paramFetcher->get('page');
        $active = strtolower($paramFetcher->get('active'));

        $repo = $this->getDoctrine()->getRepository(EventScheduleSendSurvey::class);

        $query = $repo->filter(
            $filter,
            $orderBy,
            $orderDirection,
            $active,
            $this->getUser()
        );

        $eventSchedules = new EventSchedules(
            $query,
            $repo->getCount(empty($active) ? null : $active),
            $limit,
            $page
        );

        return $this->view($eventSchedules, Response::HTTP_OK);
    }
}
