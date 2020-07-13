<?php

namespace AppBundle\Command;

use AppBundle\Entity\BranchProfile;
use AppBundle\Entity\CompanyProfile;
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AverageRatingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('eendorsements:averageRating')
            ->setDescription('Command script to calculate and Store Average rating and Scorable Endorsements of all the profiles (user/branch/company) of given user')
            ->addArgument('userId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);

        $container = $this->getContainer();

        if ($container->getParameter('crons_enabled') == "true" || $container->getParameter('ng') == "https://staging.eendorsements.com") {

            $userId = $input->getArgument('userId');
            $output->writeln("====Average Rating Command for ".$userId." user is Started====");

            $em = $container->get('doctrine.orm.entity_manager');
            $userRepo = $em->getRepository(User::class);
            $user = $userRepo->find($userId);
            $branch = $user->getBranch();
            $company = $branch->getCompany();

            $statService = $container->get('statistic_service');

            /** Update average scores and scorable endorsements in user profile */
            $userProfile = $em->getRepository(UserProfile::class)->getProfileByUserSlug($user->getSlug());
            if($userProfile) {
                $output->writeln("====User Profile Found====");
                $userAverageScore = $statService->calculateAverageScoreByUser($user);
                $userScorableEndorsements = $statService->countScorableEndorsementsByUser($user);
                $userProfile->setAverageRating($userAverageScore);
                $userProfile->setScorableEndorsements($userScorableEndorsements);
                $em->persist($userProfile);
                $em->flush();
                $output->writeln("====Average rating is calculated and saved====");
            }

            /** Update average scores and scorable endorsements in branch profile */
            $branchProfile = $em->getRepository(BranchProfile::class)->getProfileByBranchSlug($branch->getSlug());
            if($branchProfile) {
                $output->writeln("====Branch Profile Found====");
                $branchAverageScore = $statService->calculateAverageScoreByBranch($branch);
                $branchScorableEndorsements = $statService->countScorableEndorsementsByBranch($branch);
                $branchProfile->setAverageRating($branchAverageScore);
                $branchProfile->setScorableEndorsements($branchScorableEndorsements);
                $em->persist($branchProfile);
                $em->flush();
                $output->writeln("====Average rating is calculated and saved====");
            }

            /** Update average scores and scorable endorsements in company profile */
            $companyProfile = $em->getRepository(CompanyProfile::class)->getProfileByCompanySlug($company->getSlug());
            if($companyProfile) {
                $output->writeln("====Company Profile Found====");
                $companyAverageScore = $statService->calculateAverageScoreByCompany($company);
                $companyScorableEndorsements = $statService->countScorableEndorsementsByCompany($company);
                $companyProfile->setAverageRating($companyAverageScore);
                $companyProfile->setScorableEndorsements($companyScorableEndorsements);
                $em->persist($companyProfile);
                $em->flush();
                $output->writeln("====Average rating is calculated and saved====");
            }

            $output->writeln("====Average Rating Command is Ended====");
        }
    }
}