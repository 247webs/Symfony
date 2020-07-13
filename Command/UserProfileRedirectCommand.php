<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserProfileRedirectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:generateprofileredirects')
            ->setDescription('Generates redirects from old profile url pattern to new user profile pattern');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hola, vamanos a escribir...');

        $file = fopen(__DIR__ . '/../../../data/redirects/profile-redirects.txt', 'w+');

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $userRepo = $em->getRepository('AppBundle:User');
        $users = $userRepo->createQueryBuilder('u')
            ->getQuery()
            ->getResult();

        $output->writeln(sprintf('%d users identified', sizeof($users)));

        /** @var User $user */
        foreach ($users as $user) {
            fwrite(
                $file,
                "Redirect 301 /" . $user->getSlug() . " https://eendorsements.com/user/" . $user->getSlug() . "\n"
            );
        }

        $output->writeln("Ja!");
    }
}
