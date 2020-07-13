<?php

namespace AppBundle\Command;

use AppBundle\Entity\OpendentalApikey;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpendentalImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eendorsements:opendentalcontacts')
            ->setDescription('Import contacts from Opendental');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0); // Let it rip!

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true") {
            $opendentalService = $container->get('opendental_service');
            $contactService = $container->get('contact_service');
            $em = $container->get('doctrine.orm.entity_manager');

            // Get integrated users
            $opendentalApikeys = $em->getRepository(OpendentalApikey::class)->findAll();

            /** @var OpendentalApikey $opendentalApikey */
            foreach ($opendentalApikeys as $opendentalApikey) {

                // Get Appointments
                $appointments = $opendentalService->getAppointments($opendentalApikey);

                // If loans are closed or funded, grab borrower info and create a contact
                if ($appointments['entry']) {
                    foreach ($appointments['entry'] as $appointment) {
                        $patientId = $practitionerId = "";
                        $participants = $appointment['resource']['participant'];
                        foreach ($participants as $participant) {
                            $referenceArray = explode("/", $participant['actor']['reference']);
                            if($referenceArray[0] == 'Patient') {
                                $patientId = $referenceArray[1];
                            }
                            else if($referenceArray[0] == 'Practitioner'){
                                $practitionerId = $referenceArray[1];
                            }
                        }

                        if ($patientId && $practitionerId) {
                            $endorsementPractitioner = $opendentalService->getEndorsementPractitioner($opendentalApikey, $practitionerId);
                            if ($endorsementPractitioner) {
                                $user = $endorsementPractitioner->getUser();
                                $patient = $opendentalService->getPatient($opendentalApikey, $patientId);
                                $patientContactInfo = $this->parseContactInfo($patient);
                                if ($patientContactInfo) {
                                    $contact = $contactService->createContactFromOpendentalPatient(
                                        $patientContactInfo,
                                        $user
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $patient
     * @return array $contact
     */
    public function parseContactInfo(array $patient) {

        // Filter out minors
        if (!empty($patient['birthDate']) && (strtotime($patient['birthDate']) > strtotime("-18 years"))) {
            return false;
        }
        $contact = [];
        $contact['firstname'] = $patient['name'][0]['given'];
        $contact['lastname'] = $patient['name'][0]['family'];
        $patientTelecoms = (isset($patient['telecom'])) ? $patient['telecom'] : array();
        if (!empty($patientTelecoms)) {
            foreach ($patientTelecoms as $telecom) {
                if ($telecom['system'] == 'email') {
                    $contact['email'] = $telecom['value'];
                }
                else if ($telecom['system'] == 'phone') {
                    if (!isset($contact['phone'])) {
                        $contact['phone'] = $telecom['value'];
                    } else {
                        $contact['secondary_phone'] = $telecom['value'];
                    }
                }
            }
        }
        $contact['city'] = (isset($patient['address'][0]['city'])) ? $patient['address'][0]['city'] : '';
        $contact['state'] = (isset($patient['address'][0]['district'])) ? $patient['address'][0]['district'] : '';
        return $contact;
    }
}