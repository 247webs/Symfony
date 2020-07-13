<?php

namespace AppBundle\Controller;

use AppBundle\Document\OfferRequest;
use AppBundle\Document\OfferResponse;
use AppBundle\Document\Statistic\OfferResponseReceived;
use AppBundle\Entity\QuestionType\MultipleChoice;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\User;
use AppBundle\Entity\Survey;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/survey") */
class SurveyController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="survey_get", requirements={"id": "\d+"})
     * @Rest\View(serializerGroups={"default", "private"})
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Get survey",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(Survey $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Get(path="/{id}/anonymous", name="survey_get_anonymously", requirements={"id": "\d+"})
     * @Rest\View(serializerGroups={"default", "public", "profile"})
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Get survey anonymously",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAnonymouslyAction(Survey $id)
    {
        if (!$id->getUser()->getActive()) {
            throw new NotFoundHttpException("Survey not found");
        }

        return $id;
    }

    /**
     * @Rest\Put(path="/{id}", name="survey_put")
     * @Rest\View(serializerGroups={"default", "private"})
     *
     * @ParamConverter(
     *      "survey",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="survey_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Modify a survey",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data",
     *         404="Survey not found"
     *     }
     * )
     */
    public function putAction(
        Survey $id,
        Survey $survey,
        ConstraintViolationListInterface $violations
    ) {
        $this->denyAccessUnlessGranted('edit', $id);

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view($this->get('survey_service')->updateSurvey($id, $survey), Response::HTTP_OK);
    }

    /**
     * @Rest\Post(path="", name="survey_post")
     * @Rest\View(serializerGroups={"default", "private"})
     *
     * @ParamConverter(
     *      "survey",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="survey_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Create a survey",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(Survey $survey, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $survey);

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(User::class);
        $user = $repo->find($survey->getUser()->getId());

        if (!$user) {
            return $this->view(['message' => 'Invalid user'], Response::HTTP_BAD_REQUEST);
        }

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view($this->get('survey_service')->createSurvey($survey, $user), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete(path="/{id}", name="survey_delete")
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Delete a survey",
     *      https="true",
     *      statusCodes={
     *         204="Survey deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function deleteAction(Survey $id)
    {
        $this->denyAccessUnlessGranted('edit', $id);

        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get(path="/{id}/request-preview", name="survey_notification_preview")
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Survey Request Preview",
     *      https="true",
     *      statusCodes={
     *         204="Survey deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function surveyRequestPreviewAction(Survey $survey)
    {
        $this->denyAccessUnlessGranted('edit', $survey);

        $surveyLink = $this->get('survey_service')->getSurveyLink($survey, null);


        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                $user = $survey->getUser();
                $averageScore = $this->get('statistic_service')->calculateAverageScoreByUser($user);
                $averageScore = number_format((($averageScore * 100) / 20), 1);
                $feedbackLink = $this->get('survey_service')->getFeedbackLink($survey, null);
                $offersReceived = $this->get('statistic_service')
                    ->countUserStats($user, OfferResponseReceived::class);

                // Email Body
                $body = $this->render(':Email:offer_request_review_push.html.twig', [
                    'survey' => $survey,
                    'surveyLink' => $surveyLink,
                    'feedbackLink' => $feedbackLink,
                    'offersReceived' => $offersReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            case Survey::SURVEY_TYPE_VIDEOMONIAL:
                $user = $survey->getUser();
                $averageScore = $this->get('statistic_service')->calculateAverageScoreByUser($user);
                $averageScore = number_format((($averageScore * 100) / 20), 1);
                $offersReceived = $this->get('statistic_service')
                    ->countUserStats($user, OfferResponseReceived::class);

                // Email Body
                $body = $this->render(':Email:offer_request_videomonial.html.twig', [
                    'survey' => $survey,
                    'surveyLink' => $surveyLink,
                    'offersReceived' => $offersReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            default:
                // Email Body
                $body = $this->render(':Email:offer_request.html.twig', [
                    'survey' => $survey,
                    'previewMode' => true,
                    'surveyLink' => $surveyLink
                ]);
        }

        // Use subject and body now

        return $this->view(
            ['subject' => $survey->getSurveySubjectLine(), 'body' => $body->getContent()],
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Put(path="/{id}/activate", name="survey_activate")
     * @Rest\View(serializerGroups={"default", "private"})
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Activate a survey",
     *      https="true",
     *      statusCodes={
     *         204="Survey deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function activateAction(Survey $id)
    {
        $this->denyAccessUnlessGranted('edit', $id);

        $em = $this->getDoctrine()->getManager();
        $id->setActive(true);
        $em->persist($id);
        $em->flush();

        return $this->view($id, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(
     *      path="/{survey}/transfer/{owner}",
     *      name="survey_transfer",
     *      requirements={"survey": "\d+", "owner": "\d+"}
     * )
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Transfer a survey to new owner",
     *      https="true",
     *      statusCodes={
     *         202="Survey transferred",
     *         404="Not found"
     *     }
     * )
     */
    public function transferAction(Survey $survey, User $owner)
    {
        $survey->setUser($owner);

        $em = $this->getDoctrine()->getManager();
        $em->persist($survey);
        $em->flush();

        return $this->view($survey, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(path="/{survey}/make-default", name="survey_make_default", requirements={"survey": "\d+"})
     * @Rest\View(serializerGroups={"default", "private"})
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Make survey the default survey",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Not found"
     *     }
     * )
     */
    public function makeDefaultAction(Survey $survey)
    {
        $this->denyAccessUnlessGranted('edit', $survey);

        $survey->setIsDefault(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($survey);
        $em->flush();

        $this->get('survey_service')->manageDefaultSurvey($survey);

        return $this->view($survey, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(path="/{id}/global/{action}", name="survey_global")
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Activate a survey",
     *      https="true",
     *      statusCodes={
     *         204="Survey deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function globalAction(Survey $id, $action)
    {
        $this->denyAccessUnlessGranted('edit', $id);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var boolean $makeGlobal */
        $makeGlobal = (strtolower($action) == "true") ? true : false;

        if ($makeGlobal) {
            $surveys = $em->getRepository(Survey::class)->findBy([
                'user' => $id->getUser()
            ]);

            foreach ($surveys as $survey) {
                $survey->setIsGlobalSurvey(false);
                $em->persist($survey);
            }
        }

        $id->setIsGlobalSurvey($makeGlobal);

        $em->persist($id);
        $em->flush();

        return $this->view($id, Response::HTTP_OK);
    }

    /**
     * @Rest\Get(path="/{id}/has-responses", name="survey_has_responses_get", requirements={"id": "\d+"})
     *
     * @Doc\ApiDoc(
     *      section="Survey",
     *      description="Get survey responses",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function hasOfferResponses(Survey $survey)
    {
        $offerResponses = $this->get('survey_service')->getOfferResponsesBySurvey($survey);

        return $this->view(['has_responses' => (count($offerResponses)) ? true : false], Response::HTTP_OK);
    }
}
