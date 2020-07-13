<?php

namespace AppBundle\Command;

use AppBundle\Entity\MindbodyToken;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MindbodyImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eoffers:importmindbodycontacts')
            ->setDescription('Import contacts from Mindbody');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);

        $container = $this->getContainer();

        $output->writeln('====== Command for Import clients from Mindbody Completed Appointments ======');

        if ($container->getParameter('crons_enabled') == "true") {
            $mindbodyService = $container->get('mindbody_service');
            $contactService = $container->get('contact_service');
            $em = $container->get('doctrine.orm.entity_manager');

            // Get integrated users
            $mindbodyTokens = $em->getRepository(MindbodyToken::class)->findAll();

            $output->writeln("Found " . count($mindbodyTokens) . " token(s) in database.");

            /** @var MindbodyToken $mindbodyToken */
            foreach ($mindbodyTokens as $key=>$mindbodyToken) {

                $output->writeln("=========================");
                $output->writeln("Process for token  " . ($key+1) . " is started");

                $output->writeln('Mindbody API Key: ' . $mindbodyToken->getApiKey());
                $output->writeln('Mindbody Username: ' . $mindbodyToken->getUsername());

                $totalClients = 0;
                $clientsAdded = $clientsAlreadyExist = $clientsWithoutEmail = [];
        
                // Get Appointments
                $appointments = $mindbodyService->getAppointments($mindbodyToken);
                if (isset($appointments['Appointments']) && !empty($appointments['Appointments'])) {

                    $output->writeln(count($appointments['Appointments']) . ' Completed Appointments found');
                    $output->writeln("Proceeding each appointment...");

                    /** Appointment $appointment */
                    foreach ($appointments['Appointments'] as $appointment) {

                        if ($appointment['Status'] == 'Completed') {
                            $staffId = $appointment['StaffId'];
                            $clientId = $appointment['ClientId'];

                            if ($staffId && $clientId) {
                                $offerStaff = $mindbodyService->getOfferStaff($mindbodyToken, $staffId);
                                if ($offerStaff) {
                                    $user = $offerStaff->getUser();
                                    $client = $mindbodyService->getClient($mindbodyToken, $clientId); 
                                    $clientContactInfo = $this->parseContactInfo($client);
                                    if ($clientContactInfo && $clientContactInfo['email'] != null) {

                                        $exist = $contactService->isMindbodyClientExist(
                                            $clientContactInfo,
                                            $user
                                        );
                                        if($exist) {
                                            $clientsAlreadyExist[] = $clientContactInfo['firstname'] . " " . $clientContactInfo['lastname'] . " (" . $clientContactInfo['email'] . ")";
                                        } else {
                                            $contact = $contactService->createContactFromMindbodyClient(
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
                $output->writeln('total_clients : ' . $totalClients);
                $output->writeln('clients_added : ' . json_encode($clientsAdded));
                $output->writeln('clients_already_exist : ' . json_encode($clientsAlreadyExist));
                $output->writeln('clients_without_email : ' . json_encode($clientsWithoutEmail));
                $output->writeln("Process for token  " . ($key+1) . " is ended");
                $output->writeln("=========================");
            }
        }
    }

    /**
     * @param $client
     * @return array $contact
     */
    public function parseContactInfo($client) {
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
}