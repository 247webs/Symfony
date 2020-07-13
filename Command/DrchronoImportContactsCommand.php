<?php

namespace AppBundle\Command;

use AppBundle\Entity\DrchronoPractice;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DrchronoImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eendorsements:importdrchronocontacts')
            ->setDescription('Import contacts from Drchrono');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0); // Let it rip!

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true") {
            $drchronoService = $container->get('drchrono_service');
            $contactService = $container->get('contact_service');
            $em = $container->get('doctrine.orm.entity_manager');
            $yesterday = new \DateTime('yesterday');
            $since = $yesterday->format('Y-m-d 00:00:00');

            // Get integrated users
            $drchronoPractices = $em->getRepository(DrchronoPractice::class)->findAll();

            /** @var DrchronoPractice $drchronoPractice */
            foreach ($drchronoPractices as $drchronoPractice) {
                // Get completed appointments
                $appointments = $drchronoService->getAppointments($drchronoPractice, $since);

                if (!empty($appointments['results'])) {
                    foreach ($appointments['results'] as $appointment) {
                        $doctorId = $appointment['doctor'];
                        $patientId = $appointment['patient'];

                        // If Drchrono Doctor is synced with the endorsement users
                        $endorsementDoctor = $drchronoService->getEndorsementDoctor($drchronoPractice, $doctorId);
                        if ($endorsementDoctor) {
                            $user = $endorsementDoctor->getUser();

                            // Retrieve patient details
                            $patient = $drchronoService->getPatient($drchronoPractice, $patientId);
                            $patient = $this->parsePatientInfo($patient);

                            if ($patient) {
                                // Add patient as endorsement contact
                                $contactService->createContactFromDrchronoAccount(
                                    $patient,
                                    $user
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $patient
     * @return array|bool
     */
    private function parsePatientInfo($patient)
    {
        // Filter out minors
        if (!empty($patient['date_of_birth']) && (strtotime($patient['date_of_birth']) > strtotime("-18 years"))) {
            return false;
        }

        if (empty($patient['email'])) {
            return false;
        }

        $contact = [];
        $contact['firstname'] = $patient['first_name'];
        $contact['lastname'] = $patient['last_name'];
        $contact['email'] = $patient['email'];
        $contact['phone'] = (!empty($patient['home_phone'])) ? $patient['home_phone'] : $patient['cell_phone'];
        $contact['city'] = $patient['city'];
        $contact['state'] = $patient['state'];

        return $contact;
    }
}
