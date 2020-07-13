<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Referral;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/referral") */
class ReferralController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="referral_get", requirements={"id": "\d+"})
     *
     * @Rest\View(serializerGroups={"referral"})
     *
     * @Doc\ApiDoc(
     *      section="Referral",
     *      description="Get referral",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(Referral $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Post(path="", name="referral_post")
     *
     * @Rest\View(serializerGroups={"referral"})
     *
     * @ParamConverter(
     *      "referral",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="referral_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Referral",
     *      description="Create a referral",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(Referral $referral, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $em = $this->getDoctrine()->getManager();

        $referral->setActive(true);
        $referral->setUser($em->getReference('AppBundle:User', $referral->getUser()->getId()));

        $em->persist($referral);
        $em->flush();

        return $this->view($referral, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete(path="/{id}", name="referral_delete", requirements={"id": "\d+"})
     *
     * @Rest\View(serializerGroups={"referral"})
     *
     * @Doc\ApiDoc(
     *      section="Referral",
     *      description="Delete a referral",
     *      https="true",
     *      statusCodes={
     *         204="Referral deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function deleteAction(Referral $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }
}
