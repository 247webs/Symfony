<?php

namespace AppBundle\Controller;

use AppBundle\Entity\EventScheduler\EventScheduleSendSurvey;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/event-schedule") */
class EventScheduleController extends FOSRestController
{
    /**
     * @Rest\Get(path="/send-survey/{id}", name="event_schedule_send_survey_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Event Schedule",
     *      description="Retrieve an event schedule for sending surveys",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Event Schedule not found"
     *     }
     * )
     */
    public function sendSurveyGetAction(EventScheduleSendSurvey $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Put(path="/send-survey/{id}", name="event_schedule_send_survey_put")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "eventSchedule",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="scheduled_event_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Event Schedule",
     *      description="Modify an event schedule for sending surveys",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data",
     *         404="Event Schedule not found"
     *     }
     * )
     */
    public function sendSurveyPutAction(
        EventScheduleSendSurvey $id,
        EventScheduleSendSurvey $eventSchedule,
        ConstraintViolationListInterface $violations
    ) {
        $this->denyAccessUnlessGranted('edit', $id);

        if (count($violations)) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
            throw new ApiProblemException($apiProblem);
        }

        return $this->view(
            $this->get('event_scheduler_send_survey_service')->updateSendSurveyEventSchedule($id, $eventSchedule),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post(path="/send-survey", name="event_schedule_send_survey_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "eventSchedule",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="scheduled_event_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Event Schedule",
     *      description="Create an event schedule for sending surveys",
     *      https="true",
     *      statusCodes={
     *         201="Created",
     *         400="Invalid data"
     *     }
     * )
     */
    public function sendSurveyPostAction(
        EventScheduleSendSurvey $eventSchedule,
        ConstraintViolationListInterface $violations
    ) {
        if (count($violations)) {
            $apiProblem = new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR);
            throw new ApiProblemException($apiProblem);
        }

        return $this->view(
            $this->get('event_scheduler_send_survey_service')->createSendSurveyEventSchedule($eventSchedule),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Delete(path="/send-survey/{id}", name="event_schedule_send_survey_delete")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Event Schedule",
     *      description="Delete an event schedule for sending surveys",
     *      https="true",
     *      statusCodes={
     *         204="Success",
     *         404="Event Schedule not found"
     *     }
     * )
     */
    public function sendSurveyDeleteAction(EventScheduleSendSurvey $id)
    {
        $this->denyAccessUnlessGranted('delete', $id);

        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }
}
