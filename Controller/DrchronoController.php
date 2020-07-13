<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DrchronoPractice;
use AppBundle\Entity\DrchronoDoctor;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @Route("/drchrono") */
class DrchronoController extends FOSRestController
{
    /**
     * @Rest\Post(path="/authorize", name="drchrono_authorize_post")
     *
     * @ParamConverter(
     *     "branch",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Rest\QueryParam(
     *      name="auth_code",
     *      default=null,
     *      description="Auth code"
     * )
     *
     * @Rest\QueryParam(
     *      name="redirect_uri",
     *      default=null,
     *      description="Redirect Uri"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Authorize and Get Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function authorize(ParamFetcherInterface $paramFetcher)
    {
        $auth_code = $paramFetcher->get('auth_code');
        $redirect_uri = $paramFetcher->get('redirect_uri');
        $company = $this->getUser()->getBranch()->getCompany();

        $token = $this->get('drchrono_service')->generateToken($auth_code, $redirect_uri, $company);

        return $this->view(
            ['message' => $token ? 'success' : 'fail'],
            $token ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Rest\Get(path="/token", name="drchrono_token_get")
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Get Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getToken()
    {
        $token = $this->getDoctrine()->getRepository(DrchronoPractice::class)->findOneBy([
            'company' => $this->getUser()->getBranch()->getCompany()->getId()
        ]);

        if (!$token) {
            throw new NotFoundHttpException('User does not have an Drchrono token');
        }

        $this->denyAccessUnlessGranted('view', $token);

        return $token;
    }

    /**
     * @Rest\Get(path="/{practice}/doctors", name="drchrono_get_doctors")
     *
     * @Doc\ApiDoc(
     *     section="Drchrono",
     *     description="Get Doctors",
     *     https="true",
     *     statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getDoctorsAction(DrchronoPractice $practice)
    {
        $this->denyAccessUnlessGranted('view', $practice);

        return $this->get('drchrono_service')->getDoctors($practice);
    }

    /**
     * @Rest\Get("/{practice}/mapped-doctors", name="drchrono_mapped_doctors_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Get a list of doctors mapped to a Drchrono account",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getMappedDoctorsAction(DrchronoPractice $practice)
    {
        $this->denyAccessUnlessGranted('view', $practice);

        return $this->get('drchrono_service')->getMappedDoctors($practice);
    }

    /**
     * @Rest\Post("/{practice}/doctors", name="drchrono_doctors_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Map doctors to the eEndorsement users",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postDoctorsAction(DrchronoPractice $practice, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $practice);

        $data = json_decode($request->getContent(), true);
        $valid = [];
        $invalid = [];

        foreach ($data as $doctor) {
            $p = $this->get('drchrono_service')->createDoctorFromArray($practice, $doctor);
            //$valid[] = $p;
            $errors = $this->get('validator')->validate($p, null, ["drchrono_doctor_post"]);

            (count($errors)) ?
                $invalid[] = $p->getUser()->getFirstName() . ' ' . $p->getUser()->getLastName() :
                $valid[] = $p;
        }

        $results = $this->get('drchrono_service')->addDoctors($practice, $valid);

        // handle any other fails
        $errors = [];
        if (count($results->notAdded)) {
            $errors = array_merge($invalid, $results->notAdded);
        }

        return $this->view(
            ['doctors_created' => $results->added, 'invalid_doctors' => $errors],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Put("/{practice}/appointment_status/{appointment_status}", name="drchrono_appointment_status_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Update appointment status",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postAppointmentStatusAction(DrchronoPractice $practice, string $appointment_status)
    {
        $practice = $this->get('drchrono_service')->setAppointmentStatus($practice, $appointment_status);

        return $practice;
    }

    /**
     * @Rest\Delete(path="/{practice}", name="drchrono_practice_delete")
     *
     * @Doc\ApiDoc(
     *      section="Drchrono",
     *      description="Delete Practice",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function deletePracticeAction(DrchronoPractice $practice)
    {
        $this->denyAccessUnlessGranted('edit', $practice);

        // Delete doctors of the given drchrono account
        $this->deleteDoctorsAction($practice);

        // Delete drchrono account
        $this->getDoctrine()->getManager()->remove($practice);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param DrchronoPractice $practice
     * @return void
     */
    private function deleteDoctorsAction(DrchronoPractice $practice)
    {
        $doctors = $this->getDoctrine()->getRepository(DrchronoDoctor::class)->findBy([
            'drchronoPractice' => $practice
        ]);

        if (count($doctors)) {
            foreach ($doctors as $doctor) {
                $this->getDoctrine()->getManager()->remove($doctor);
            }

            $this->getDoctrine()->getManager()->flush();
        }
    }
}
