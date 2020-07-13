<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Preferences\BranchPreferences;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use AppBundle\Repository\BranchPreferencesRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/branch-preferences") */
class BranchPreferencesController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="branch_preferences_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *     "id",
     *     class="AppBundle\Entity\Preferences\BranchPreferences",
     *     options={
     *          "repository_method" = "getPreferencesByIdOrBranchSlug",
     *          "mapping" = {"id": "id"},
     *          "map_method_signature" = true
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Preferences",
     *      description="Get branch preferences",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(BranchPreferences $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $id;
    }

    /**
     * @Rest\Put(path="/{id}", name="branch_preferences_put")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "preferences",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="branch_preferences_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Preferences",
     *      description="Update branch preferences",
     *      https="true",
     *      statusCodes={
     *         200="Preferences Updated",
     *         400="Invalid data"
     *     }
     * )
     *
     */
    public function putAction(
        BranchPreferences $id,
        BranchPreferences $preferences,
        ConstraintViolationListInterface $violations
    ) {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $this->denyAccessUnlessGranted('edit', $id);

        return $this->view(
            $this->get('preferences_service')->updateBranchPreferences($id, $preferences),
            Response::HTTP_OK
        );
    }

    /**
     * @Rest\Post(path="", name="branch_preferences_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "preferences",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="branch_preferences_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Preferences",
     *      description="Create branch preferences",
     *      https="true",
     *      statusCodes={
     *         201="Preferences Created",
     *         400="Invalid data"
     *     }
     * )
     *
     */
    public function postAction(BranchPreferences $preferences, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $this->denyAccessUnlessGranted('edit', $preferences);

        $em = $this->getDoctrine()->getManager();

        /** @var BranchPreferencesRepository $repo */
        $repo = $em->getRepository('AppBundle:Preferences\BranchPreferences');

        $q = $repo->findOneBy(['branch' => $preferences->getBranch()->getId()]);

        if ($q) {
            return $this->forward('AppBundle:BranchPreferences:put', ['id' => $q->getId()]);
        }

        return $this->view(
            $this->get('preferences_service')->createBranchPreferences($preferences),
            Response::HTTP_CREATED
        );
    }
}
