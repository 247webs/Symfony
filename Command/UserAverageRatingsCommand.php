<?php

namespace AppBundle\Command;

use AppBundle\Entity\UserProfile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserAverageRatingsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eoffers:calculateUserAverageRating')
            ->setDescription('One time command script to calculate and Store Average rating and Scorable Offers of existing users');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true") {

            $output->writeln("=====User Average Rating Command is Started=====");

            $statService = $container->get('statistic_service');
            $em = $container->get('doctrine.orm.entity_manager');

            $userProfilesRepo = $em->getRepository(UserProfile::class);
            $qb = $userProfilesRepo->createQueryBuilder('profile');
            $profiles = $qb
                ->where($qb->expr()->eq('profile.temp_rating_proceeded', ':temp_rating_proceeded'))
                ->setParameter('temp_rating_proceeded', 0)
                ->getQuery()
                ->setMaxResults(50)
                ->getResult();

            if (count($profiles)) {
                $batchTrigger = 5;
                $count = 1 ;
                foreach ($profiles as $profile) {
                    $output->writeln("Calculating Average Rating of User Profile Id: " . $profile->getId());

                    $averageScore = $statService->calculateAverageScoreByUser($profile->getUser());
                    if($averageScore) {
                        $scorableOffers = $statService->countScorableOffersByUser($profile->getUser());
                        $profile->setAverageRating($averageScore);
                        $profile->setScorableOffers($scorableOffers);
                    }
                    $profile->setTempRatingProceeded(1);
                    $em->persist($profile);
                    if($count%$batchTrigger == 0 || count($profiles) < $batchTrigger) {
                        $em->flush();
                    }
                    $count++;
                }
                $em->clear();
            }
            $output->writeln("=====User Average Rating Command is Ended=====");
        }
    }
}