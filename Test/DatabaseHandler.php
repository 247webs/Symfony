<?php

namespace AppBundle\Test;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

class DatabaseHandler
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param Connection $connection
     * @param KernelInterface $kernel
     */
    public function __construct(Connection $connection, KernelInterface $kernel)
    {
        $this->connection = $connection;
        $this->kernel = $kernel;
    }

    public function reloadTestSchema()
    {
        $application = new Application($this->kernel);
        $application->add(new DropSchemaDoctrineCommand());
        $application->add(new CreateSchemaDoctrineCommand());
        $application->add(new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand());
        $application->add(new \Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand());

        $command = $application->find('doctrine:schema:drop');

        $runner = new CommandTester($command);
        $runner->execute([
            'command' => $command->getName(),
            '--force' => true,
        ]);

        $command = $application->find('doctrine:schema:create');

        $runner = new CommandTester($command);
        $runner->execute([
            'command' => $command->getName(),
        ]);
    }
}