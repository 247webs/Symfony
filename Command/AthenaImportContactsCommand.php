<?php

namespace AppBundle\Command;

use AppBundle\Entity\AthenaProvider;
use AppBundle\Services\AthenaHealthService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AthenaImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eendorsements:importathenacontacts')
            ->setDescription('Import contacts from Athena');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0); // Let it rip!

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true") {
            $athenaHealthService = $container->get('athena_health_service');
            $em = $container->get('doctrine.orm.entity_manager');
            $contactService = $container->get('contact_service');
            $yesterday = new \DateTime('yesterday');


            // Get integrated users
            $athenaProviders = $em->getRepository(AthenaProvider::class)->findAll();

            /** @var AthenaProvider $provider */
            foreach ($athenaProviders as $provider) {
                $practiceId = $provider->getAthenaPractice()->getAthenaPracticeId();

                // Get appointments that have been checked out
                $appointments = $athenaHealthService->getAppointments(
                    $practiceId,
                    $yesterday,
                    $yesterday,
                    $provider->getAthenaProviderId(),
                    AthenaHealthService::APPOINTMENT_STATUS_CHECKED_OUT
                );

                // If appointments have been closed, grab patient info and create a contact
                if (count($appointments['totalcount'])) {
                    foreach ($appointments['appointments'] as $appointment) {
                        $patientId = $appointment['patientid'];

                        // Get patient details from Athena
                        $patients = $athenaHealthService->getPatient($practiceId, $patientId);

                        // Athena returns an array for each patient
                        if (count($patients)) {
                            $contactService->createContactFromAthenaPatient(
                                $patients[0],
                                $provider->getUser()
                            );
                        }
                    }
                }
            }
        }
    }
}
