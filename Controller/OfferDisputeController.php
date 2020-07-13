<?php

namespace AppBundle\Controller;

use AppBundle\Document\OfferDispute;
use AppBundle\Document\OfferResponse;
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

/** @Route("/offer-dispute") */
class OfferDisputeController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="offer_dispute_get")
     *
     * @Doc\ApiDoc(
     *      section="Offer Dispute",
     *      description="Get an offer dispute",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         403="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(OfferDispute $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Put(path="/{id}", name="offer_dispute_put")
     *
     * @ParamConverter(
     *      "offerDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="offer_dispute_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Offer Dispute",
     *      description="Modify an offer dispute",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function putAction(
        OfferDispute $id,
        OfferDispute $offerDispute,
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
            $this->get('offer_dispute_service')->update($id, $offerDispute),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post(path="", name="offer_dispute_post")
     *
     * @ParamConverter(
     *      "offerDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="offer_dispute_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Offer Dispute",
     *      description="Create an offer dispute",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(OfferDispute $offerDispute, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $offerDispute);

        // Validate the request body
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $offerDisputeService = $this->get('offer_dispute_service');

        // Create the dispute
        $created = $offerDisputeService->create($offerDispute);

        // Notify admin
        $this->get('mailer')->sendOfferDisputeToAdmin(
            $offerDisputeService->getAdminDisputeUri($created),
            $this->getParameter('admin_email')
        );

        return $this->view($created, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Put(path="/{id}/put-status", name="offer_dispute_status")
     *
     * @ParamConverter(
     *      "offerDispute",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="offer_dispute_status"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Offer Dispute",
     *      description="Set status to Offer dispute",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     *
     */
    public function putStatus(
        OfferDispute $id,
        OfferDispute $offerDispute,
        ConstraintViolationListInterface $violations
    ) {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT)
            );
        }

        /** @var OfferDispute $updated */
        $updated = $this->get('offer_dispute_service')->setStatus($id, $offerDispute);

        /** @var OfferResponse $offerResponse */
        $offerResponse = $updated->getOfferResponse();

        /** @var Survey $survey */
        $survey = $this->getDoctrine()->getRepository(Survey::class)->find(
            $offerResponse->getOfferRequest()->getSurveyId()
        );

        /** @var User $user */
        $user = $survey->getUser();

        /** Set a new background job to calculate average rating of all the profiles of survey user */
        $em = $this->getDoctrine()->getManager();
        $job = new Job('eoffers:averageRating', array($user->getId()));
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
        $this->get('mailer')->sendOfferDisputeUpdate(
            $updated,
            $survey,
            array_unique($to),
            $this->get('offer_response_service')->formatOfferResponseForEMail($offerResponse)
        );

        return $this->view($updated, Response::HTTP_OK);
    }
}
