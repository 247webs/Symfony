<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NoahsArkMigrationCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:migratenoahsark')
            ->setDescription('Migrates data from the old system to the configured database for Noahs Ark')
            ->addArgument('user', InputArgument::REQUIRED)
            ->addArgument('action', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0); // Let it rip!

        if ('offers' === $input->getArgument('action')) {
            $this->getContainer()->get('noahs_ark_service')->migrate($input->getArgument('user'));
        }

        if ('contacts' === $input->getArgument('action')) {
            $this->getContainer()->get('noahs_ark_service')->createContacts($input->getArgument('user'));
        }
    }
}
