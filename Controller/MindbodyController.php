<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MindbodyToken;
use AppBundle\Entity\MindbodyStaff;
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

/** @Route("/mindbody") */
class MindbodyController extends FOSRestController
{
    /**
     * @Rest\Post(path="/authorize", name="mindbody_authorize_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "mindbodyToken",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="mindbody_authorize_post"
     *          }
     *      }
     * )
     * @Doc\ApiDoc(
     *      section="mindbodyToken",
     *      description="mindbody Apikey authorize",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function authorize(MindbodyToken $mindbodyToken)
    {
        if ($mindbodyToken->getApiKey() == null) {
            throw new BadRequestHttpException("Api Key is required");
        }
        if ($mindbodyToken->getSiteId() == null) {
            throw new BadRequestHttpException("Site Id is required");
        }
        if ($mindbodyToken->getUsername() == null) {
            throw new BadRequestHttpException("Username is required");
        }
        if ($mindbodyToken->getPassword() == null) {
            throw new BadRequestHttpException("Password is required");
        }

        $company = $this->getUser()->getBranch()->getCompany();
        $token = $this->get('mindbody_service')->generateToken($mindbodyToken, $company);

        return $this->view(
            ['message' => ($token) ? 'success' : 'fail'],
            ($token) ? Response::HTTP_CREATED : Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @Rest\Get(path="", name="mindbody_token_get")
     *
     * @Doc\ApiDoc(
     *      section="mindbodyToken",
     *      description="Get Mindbody Token",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getMindbodyToken()
    {
        $token = $this->getDoctrine()->getRepository(MindbodyToken::class)->findOneBy([
            'company' => $this->getUser()->getBranch()->getCompany()->getId()
        ]);
        if (!$token) {
            throw new NotFoundHttpException('User does not have Mindbody Token');
        }

        $this->denyAccessUnlessGranted('view', $token);

        return $token;
    }


    /**
     * @Rest\Get(path="/{mindbodyToken}/staffmembers", name="mindbody_get_staff_members")
     *
     * @Doc\ApiDoc(
     *     section="Mindbody",
     *     description="Get Staff Members",
     *     https="true",
     *     statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getStaffMembersAction(MindbodyToken $mindbodyToken)
    {
        $this->denyAccessUnlessGranted('view', $mindbodyToken);

        return $this->get('mindbody_service')->getStaffMembers($mindbodyToken);
    }

    /**
     * @Rest\Get("/{mindbodyToken}/mapped-staff-members", name="mindbody_mapped_staff_members_get")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Mindbody",
     *      description="Get a list of staff members mapped to the eEndorsements users",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function getMappedStaffMembersAction(MindbodyToken $mindbodyToken)
    {
        $this->denyAccessUnlessGranted('view', $mindbodyToken);

        return $this->get('mindbody_service')->getMappedStaffMembers($mindbodyToken);
    }

    /**
     * @Rest\Post("/{mindbodyToken}/map-staff-members", name="mindbody_map_staff_members_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Mindbody",
     *      description="Map staff members to the eEndorsement users. Will truncate current mapped staff members.",
     *      https="true",
     *      statusCodes={
     *         201 = "Returned when successful",
     *         400 = "Bad request",
     *         401 = "Unauthorized"
     *     }
     * )
     */
    public function postMapStaffMembersAction(MindbodyToken $mindbodyToken, Request $request)
    {
        $this->denyAccessUnlessGranted('edit', $mindbodyToken);

        $data = json_decode($request->getContent(), true);
        $valid = [];
        $invalid = [];

        foreach ($data as $staffmember) {
            $o = $this->get('mindbody_service')->createStaffMemberFromArray($mindbodyToken, $staffmember);

            $errors = $this->get('validator')->validate($o, null, ["mindbody_staff_post"]);

            (count($errors)) ?
                $invalid[] = $o->getUser()->getFirstName() . ' ' . $o->getUser()->getLastName() :
                $valid[] = $o;
        }

        $results = $this->get('mindbody_service')->mapStaffMembers($mindbodyToken, $valid);

        // handle any other fails
        $errors = [];
        if (count($results->notAdded)) {
            $errors = array_merge($invalid, $results->notAdded);
        }

        return $this->view(
            ['staffmembers_created' => $results->added, 'invalid_staffmembers' => $errors],
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Get(path="/import-clients/{mindbodyToken}", name="mindbody_import_clients_get")
     *
     * @Doc\ApiDoc(
     *      section="MindbodyToken",
     *      description="Import Mindbody Clients",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getMindbodyImportClientsAction(MindbodyToken $mindbodyToken)
    {
        $this->denyAccessUnlessGranted('edit', $mindbodyToken);

        $totalClients = 0;
        $clientsAdded = $clientsAlreadyExist = $clientsWithoutEmail = [];

        // Get Appointments
        $appointments = $this->get('mindbody_service')->getAppointments($mindbodyToken);
        if (isset($appointments['Appointments']) && !empty($appointments['Appointments'])) {
            /** Appointment $appointment */
            foreach ($appointments['Appointments'] as $appointment) {

                if ($appointment['Status'] == 'Completed') {
                    $staffId = $appointment['StaffId'];
                    $clientId = $appointment['ClientId'];

                    if ($staffId && $clientId) {
                        $endorsementStaff = $this->get('mindbody_service')->getEndorsementStaff($mindbodyToken, $staffId);
                        if ($endorsementStaff) {
                            $user = $endorsementStaff->getUser();
                            $client = $this->get('mindbody_service')->getClient($mindbodyToken, $clientId); 
                            $clientContactInfo = $this->parseContactInfo($client);
                            if ($clientContactInfo && $clientContactInfo['email'] != null) {
                                
                                $exist = $this->get('contact_service')->isMindbodyClientExist(
                                    $clientContactInfo,
                                    $user
                                );
                                if($exist) {
                                    $clientsAlreadyExist[] = $clientContactInfo['firstname'] . " " . $clientContactInfo['lastname'] . " (" . $clientContactInfo['email'] . ")";
                                } else {
                                    $contact = $this->get('contact_service')->createContactFromMindbodyClient(
                                        $clientContactInfo,
                                        $user
                                    );
                                    $clientsAdded[] = $clientContactInfo['firstname'] . " " . $clientContactInfo['lastname'] . " (" . $clientContactInfo['email'] . ")";
                                }
                            } else {
                                $clientsWithoutEmail[] = $clientContactInfo['firstname'] . " " . $clientContactInfo['lastname'];
                            }
                        }
                        $totalClients++;
                    }
                }
            }
        }
        return $this->view(['total_clients' => $totalClients, 'clients_added' => $clientsAdded, 'clients_already_exist' => $clientsAlreadyExist, 'clients_without_email' => $clientsWithoutEmail], Response::HTTP_OK);
    }

    /**
     * @param $client
     * @return array $contact
     */
    private function parseContactInfo($client) {
        if (!isset($client['Clients']) || empty($client['Clients'])) {
            return false;
        }
        $client                     = $client['Clients'][0];
        $contact                    = [];
        $contact['firstname']       = $client['FirstName'];
        $contact['lastname']        = $client['LastName'];
        $contact['email']           = $client['Email'];
        $contact['phone']           = (!empty($client['HomePhone'])) ? $client['HomePhone'] : $client['WorkPhone'];
        $contact['secondary_phone'] = $client['MobilePhone'];
        $contact['city']            = (isset($client['City'])) ? $client['City'] : '';
        $contact['state']           = (isset($client['State'])) ? $client['State'] : '';
        $contact['do_not_text']     = (isset($client['SendAccountTexts']) && empty($client['SendAccountTexts'])) ? true : false;
        return $contact;
    }
    
    /**
     * @Rest\Delete(path="/{mindbodyToken}", name="mindbody_token_delete")
     *
     * @Doc\ApiDoc(
     *      section="MindbodyToken",
     *      description="Delete MindbodyToken",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function deleteMindbodyTokenAction(MindbodyToken $mindbodyToken)
    {
        $this->denyAccessUnlessGranted('edit', $mindbodyToken);

        // Delete Staff members of the given Mindbody Account
        $this->deleteStaffmembersAction($mindbodyToken);

        // Delete Mindbody Token Account
        $this->getDoctrine()->getManager()->remove($mindbodyToken);
        $this->getDoctrine()->getManager()->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return void
     */
    private function deleteStaffmembersAction(MindbodyToken $mindbodyToken)
    {
        $staffmembers = $this->getDoctrine()->getRepository(MindbodyStaff::class)->findBy([
            'mindbodyToken' => $mindbodyToken
        ]);

        if (count($staffmembers)) {

            /** @var MindbodyStaff $staffmember */
            foreach ($staffmembers as $staffmember) {
                $this->getDoctrine()->getManager()->remove($staffmember);
            }

            $this->getDoctrine()->getManager()->flush();
        }
    }
}