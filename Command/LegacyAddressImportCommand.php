<?php

namespace AppBundle\Command;

use AppBundle\Migration\CSVParser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LegacyAddressImportCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eendorsements:migrateaddresses')
            ->setDescription('Migrate Addresses');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csvParser = new CSVParser();
        $candidates = $csvParser->parse([
            fopen(__DIR__ . '/../../../data/migration/Direct transfers.csv', 'r'),
            fopen(__DIR__ . '/../../../data/migration/Reseller transfers.csv', 'r'),
        ]);

        $output->writeln('Initializing...');
        $output->writeln(sprintf('%d primary accounts have been selected.', sizeof($candidates)));

        $addressMigrator = $this->getContainer()->get('legacy_address_migrator');

        foreach ($candidates as $candidate) {
            $addressMigrator->migrate($candidate['userId']);
        }
    }
}
