<?php

namespace AppBundle\Controller;

use AppBundle\Document\EndorsementDispute;
use AppBundle\Document\EndorsementResponse;
use AppBundle\Entity\Survey;
use AppBundle\Entity\User;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use AppBundle\Repository\UserRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\JobQueueBundle\Entity\Job;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/endorsement-dispute") */
class EndorsementDisputeController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="endorsement_dispute_get")
     *
     * @Doc\ApiDoc(
     *      section="Endorsement Dispute",
     *      description="Get an endorsement dispute",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         403="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(EndorsementDispute $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Put(path="/{id}", name="endorsement_dispute_put")
     *
     * @ParamConverter(
     *      "endorsementDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="endorsement_dispute_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Endorsement Dispute",
     *      description="Modify an endorsement dispute",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function putAction(
        EndorsementDispute $id,
        EndorsementDispute $endorsementDispute,
        ConstraintViolationListInterface $violations
    ) {
        $this->denyAccessUnlessGranted('edit', $id);

        // Validate the request body
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view(
            $this->get('endorsement_dispute_service')->update($id, $endorsementDispute),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post(path="", name="endorsement_dispute_post")
     *
     * @ParamConverter(
     *      "endorsementDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="endorsement_dispute_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Endorsement Dispute",
     *      description="Create an endorsement dispute",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(EndorsementDispute $endorsementDispute, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $endorsementDispute);

        // Validate the request body
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $endorsementDisputeService = $this->get('endorsement_dispute_service');

        // Create the dispute
        $created = $endorsementDisputeService->create($endorsementDispute);

        // Notify admin
        $this->get('mailer')->sendEndorsementDisputeToAdmin(
            $endorsementDisputeService->getAdminDisputeUri($created),
            $this->getParameter('admin_email')
        );

        return $this->view($created, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put(path="/{id}/put-status", name="endorsement_dispute_status")
     *
     * @ParamConverter(
     *      "endorsementDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="endorsement_dispute_status"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Endorsement Dispute",
     *      description="Set status to Endorsement dispute",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     *
     */
    public function putStatus(
        EndorsementDispute $id,
        EndorsementDispute $endorsementDispute,
        ConstraintViolationListInterface $violations
    ) {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT)
            );
        }

        /** @var EndorsementDispute $updated */
        $updated = $this->get('endorsement_dispute_service')->setStatus($id, $endorsementDispute);

        /** @var EndorsementResponse $endorsementResponse */
        $endorsementResponse = $updated->getEndorsementResponse();

        /** @var Survey $survey */
        $survey = $this->getDoctrine()->getRepository(Survey::class)->find(
            $endorsementResponse->getEndorsementRequest()->getSurveyId()
        );

        /** @var User $user */
        $user = $survey->getUser();

        /** Set a new background job to calculate average rating of all the profiles of survey user */
        $em = $this->getDoctrine()->getManager();
        $job = new Job('eendorsements:averageRating', array($user->getId()));
        $em->persist($job);
        $em->flush($job);

        /** @var UserRepository $userRepo */
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        /** @var array $to */
        $to = [
            trim($user->getUsername()),
            trim($userRepo->getBranchAdministrator($user->getBranch())->getUsername()),
            trim($userRepo->getCompanyAdministrator($user->getBranch()->getCompany())->getUsername())
        ];

        // Update the dispute owner(s)
        $this->get('mailer')->sendEndorsementDisputeUpdate(
            $updated,
            $survey,
            array_unique($to),
            $this->get('endorsement_response_service')->formatEndorsementResponseForEMail($endorsementResponse)
        );

        return $this->view($updated, Response::HTTP_OK);
    }
}
