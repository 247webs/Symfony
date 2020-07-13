<?php

namespace AppBundle\Controller;

use AppBundle\Entity\AthenaPractice;
use AppBundle\Entity\AthenaProvider;
use AppBundle\Entity\Company;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/athena-health") */
class AthenaHealthController extends FOSRestController
{
    /**
     * @Rest\Get(path="/practice/{id}", name="athena_health_practice_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Retrieve an Athena Health Practice",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function getPractice(AthenaPractice $practice)
    {
        $this->denyAccessUnlessGranted('edit', $practice);

        return $practice;
    }

    /**
     * @Rest\Get(path="/practice/company/{company}", name="athena_health_company_practice_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Retrieve an Athena Health Company Practice",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function getCompanyPractice(Company $company)
    {
        return $company->getAthenaPractice();
    }

    /**
     * @Rest\Post(path="/practice", name="athena_health_practice_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "practice",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="athena_practice_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Create a Company to Athena Health association",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postPractice(AthenaPractice $practice, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $practice);

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        // Call Athena health service
        $result = $this->get('athena_health_service')->addPractice($practice);

        // Handle failures
        if (!$result) {
            throw new BadRequestHttpException(
                "We were unable to connect to your Athena Health practice. Please verify that you have supplied" .
                " the correct Athena Health practice ID and granted us access."
            );
        }

        return $result;
    }

    /**
     * @Rest\Delete(path="/practice/{id}", name="athena_health_practice_delete")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Delete a Company to Athena Health association.  Also remove all associated users",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function deletePractice(AthenaPractice $id)
    {
        $this->denyAccessUnlessGranted('edit', $id);

        $this->get('athena_health_service')->deletePractice($id);

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/practice/{id}/provider/{providerId}", name="athena_health_provider_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Retrieve a provider mapped to a practice",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getProviderAction(AthenaPractice $id, AthenaProvider $providerId)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $providerId;
    }

    /**
     * @Rest\Post("/practice/{id}/provider", name="athena_health_provider_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "provider",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="athena_provider_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Post a provider mapped to a practice",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postProviderAction(
        AthenaPractice $id,
        AthenaProvider $provider,
        ConstraintViolationListInterface $violations
    ) {
        $this->denyAccessUnlessGranted('view', $id);

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view($this->get('athena_health_service')->addProvider($id, $provider), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete("/practice/{id}/provider/{providerId}", name="athena_health_provider_delete")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Delete a provider mapped to a practice",
     *      https="true",
     *      statusCodes={
     *         204 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function deleteProviderAction(AthenaPractice $id, AthenaProvider $providerId)
    {
        $this->denyAccessUnlessGranted('view', $id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($providerId);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Rest\Get("/practice/{id}/athena-providers", name="athena_health_providers_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Get a list of Athena providers of given practice",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getProvidersAction(AthenaPractice $id)
    {
        $this->denyAccessUnlessGranted('view', $id);

        return $this->get('athena_health_service')->getProviders($id->getAthenaPracticeId());
    }

    /**
     * @Rest\Get("/practice/{id}/mapped-providers", name="athena_health_mapped_providers_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Get a list of Athena providers mapped to an Athena Practice",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getMappedProvidersAction(AthenaPractice $practice)
    {
        $this->denyAccessUnlessGranted('view', $practice);

        return $practice->getProviders();
    }

    /**
     * @Rest\Post("/practice/{id}/providers", name="athena_health_providers_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Athena Health",
     *      description="Add providers to an Athena Health practice. Will truncate current connected providers.",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postProvidersAction(AthenaPractice $id, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $id);

        $data = json_decode($request->getContent(), true);
        $valid = [];
        $invalid = [];

        foreach ($data as $provider) {
            $p = $this->get('athena_health_service')->createProviderFromArray($id, $provider);
            $errors = $this->get('validator')->validate($p, null, ["athena_provider_post"]);

            (count($errors)) ?
                $invalid[] = $p->getUser()->getFirstName() . ' ' . $p->getUser()->getLastName() :
                $valid[] = $p;
        }

        $results = $this->get('athena_health_service')->addProvidersToPractice($id, $valid);

        // handle any other fails
        $errors = [];
        if (count($results->notAdded)) {
            $errors = array_merge($invalid, $results->notAdded);
        }

        return $this->view(
            ['providers_created' => $results->added, 'invalid_providers' => $errors],
            Response::HTTP_CREATED
        );
    }
}
