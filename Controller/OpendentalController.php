<?php

namespace AppBundle\Controller;

use AppBundle\Entity\OpendentalApikey;
use AppBundle\Entity\OpendentalPractitioner;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @Route("/opendental") */
class OpendentalController extends FOSRestController
{
    /**
     * @Rest\Get(path="", name="opendental_apikey_get")
     *
     * @Doc\ApiDoc(
     *      section="opendentalApikey",
     *      description="Get opendental Api key",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getOpendentalApikey()
    {
        $apikey = $this->getDoctrine()->getRepository(OpendentalApikey::class)->findOneBy([
            'company' => $this->getUser()->getBranch()->getCompany()->getId()
        ]);
        if (!$apikey) {
            throw new NotFoundHttpException('User does not have opendental Apikey');
        }

        $this->denyAccessUnlessGranted('view', $apikey);

        return $apikey;
    }

    /**
     * @Rest\Post(path="/authorize", name="opendental_authorize_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "opendentalApikey",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="opendental_authorize_post"
     *          }
     *      }
     * )
     * @Doc\ApiDoc(
     *      section="opendentalApikey",
     *      description="Opendental Apikey authorize",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function authorize(OpendentalApikey $opendentalApikey) 
    {
        if ($opendentalApikey->getApiKey() == null) {
            throw new BadRequestHttpException("Api Key is required");
        }

        $isValidApikey = $this->get('opendental_service')->isValidApikey($opendentalApikey);
        if ($isValidApikey) {
            $company = $this->getUser()->getBranch()->getCompany();
            $apikey = $this->get('opendental_service')->persistApikey($opendentalApikey, $company);
        }

        return $this->view(
            ['message' => ($isValidApikey && $apikey) ? 'success' : 'fail'],
            ($isValidApikey && $apikey) ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }


    /**
     * @Rest\Get(path="/{opendentalApikey}/practitioners", name="opendental_get_practitioners")
     *
     * @Doc\ApiDoc(
     *     section="Encompass",
     *     description="Get Loan Officers",
     *     https="true",
     *     statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getPractitionersAction(OpendentalApikey $opendentalApikey)
    {
        $this->denyAccessUnlessGranted('view', $opendentalApikey);

        return $this->get('opendental_service')->getPractitioners($opendentalApikey);
    }


    /**
     * @Rest\Get("/{opendentalApikey}/mapped-practitioners", name="opendental_mapped_practitioners_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Opendental",
     *      description="Get a list of practitioners mapped to the eOffers users",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getMappedPractitionersAction(OpendentalApikey $opendentalApikey)
    {
        $this->denyAccessUnlessGranted('view', $opendentalApikey);

        return $this->get('opendental_service')->getMappedPractitioners($opendentalApikey);
    }

    /**
     * @Rest\Post("/{opendentalApikey}/map-practitioners", name="opendental_map_practitioners_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Open Dental",
     *      description="Map Practitioners to the eOffer users. Will truncate current mapped practitioners.",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postMapPractitionersAction(OpendentalApikey $opendentalApikey, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $opendentalApikey);

        $data = json_decode($request->getContent(), true);
        $valid = $invalid = [];
        foreach ($data as $practitioner) {
            $o = $this->get('opendental_service')->createPractitionerFromArray($opendentalApikey, $practitioner);
            $errors = $this->get('validator')->validate($o, null, ["opendental_practitioner_post"]);
            (count($errors)) ?
                $invalid[] = $o->getUser()->getFirstName() . ' ' . $o->getUser()->getLastName() :
                $valid[] = $o;
        }
        $results = $this->get('opendental_service')->mapPractitioners($opendentalApikey, $valid);

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

    /**
     * @Rest\Delete(path="/{opendentalApikey}", name="opendental_apikey_delete")
     *
     * @Doc\ApiDoc(
     *      section="Opendental",
     *      description="Delete Apikey",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function deleteOpendentalApikeyAction(OpendentalApikey $opendentalApikey)
    {
        $this->denyAccessUnlessGranted('edit', $opendentalApikey);

        // Delete Practitioners of the given Opendental Account
        $this->deletePractitionersAction($opendentalApikey);

        // Delete Opendental Apikey Account
        $this->getDoctrine()->getManager()->remove($opendentalApikey);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param OpendentalApikey $opendentalApikey
     * @return void
     */
    private function deletePractitionersAction(OpendentalApikey $opendentalApikey)
    {
        $practitioners = $this->getDoctrine()->getRepository(OpendentalPractitioner::class)->findBy([
            'opendentalApikey' => $opendentalApikey
        ]);
        if (count($practitioners)) {

            /** @var OpendentalPractitioner $practitioner */
            foreach ($practitioners as $practitioner) {
                $this->getDoctrine()->getManager()->remove($practitioner);
            }
            $this->getDoctrine()->getManager()->flush();
        }
    }
}