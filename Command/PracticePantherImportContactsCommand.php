<?php

namespace AppBundle\Command;

use AppBundle\Entity\PracticePantherToken;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PracticePantherImportContactsCommand
 * @package AppBundle\Command
 */
class PracticePantherImportContactsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eoffers:practice-panther-import-contacts')
            ->setDescription('Import Contacts from Practice Panther Closed Matters');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0); // Let it rip!

        $container = $this->getContainer();

//        if ($container->getParameter('crons_enabled') == "true") {
            $output->writeln('Initializing...');

            // Get all existing tokens
            $tokens = $container->get('doctrine.orm.entity_manager')
                ->getRepository(PracticePantherToken::class)
                ->findAll();

            if (count($tokens)) {
                /** @var PracticePantherToken $token */
                foreach ($tokens as $token) {
                    // Call practice panther service to get the closed matters of last 24 hours
                    $matters = $container->get('practice_panther_service')->getMatters(
                        $token,
                        null,
                        null,
                        "Closed",
                        null,
                        new \DateTime(date("Y-m-d h:i:s", strtotime('-1 day'))),
                        null,
                        null,
                        null
                    );

                    $output->writeln(sprintf('%d new closed matters found', sizeof($matters)));

                    // For each closed matter that's found, try to get retrieve a contact then persist
                    if ($matters) {
                        foreach ($matters as $matter) {
                            $accountContacts = $container->get('practice_panther_service')->getAccountContacts(
                                $token,
                                $matter->account_ref->id
                            );

                            foreach ($accountContacts as $contact) {
                                $container->get('contact_service')->createContactFromPracticePantherContact(
                                    (array) $contact,
                                    $token->getUser()
                                );

                                $output->writeln('Contact ' . $contact->id . ' imported.');
                            }
                        }
                    }
                }
            }

            $output->writeln("Practice Panther import contacts completed.");
//        }
    }
}
