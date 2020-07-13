<?php

namespace AppBundle\Command;

use AppBundle\Entity\ClioToken;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClioImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eoffers:importcliocontacts')
            ->setDescription('Import contacts from Clio');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true") {
            $clioService = $container->get('clio_service');
            $contactService = $container->get('contact_service');
            $em = $container->get('doctrine.orm.entity_manager');
            $yesterday = new \DateTime('yesterday');
            $since = $yesterday->format(\DateTime::ATOM);

            // Get integrated users
            $clioTokens = $em->getRepository(ClioToken::class)->findAll();

            /** @var ClioToken $clioToken */
            foreach ($clioTokens as $clioToken) {
                // Get closed matters
                $matters = $clioService->getClosedMatters($clioToken, $since);
                if (!empty($matters['data'])) {
                    foreach ($matters['data'] as $matter) {
                        $clioUser = $matter['responsible_attorney'];
                        $client = $matter['client'];

                        // If Clio user is mapped with the offer users
                        $mappedUser = $clioService->getMappedUser($clioToken, $clioUser['id']);
                        if ($mappedUser) {
                            $user = $mappedUser->getUser();
                            $contactService->createContactFromClioAccount($client, $user);
                        }
                    }
                }
            }
        }
    }
}