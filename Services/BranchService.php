<?php

namespace AppBundle\Services;

use AppBundle\Entity\Branch;
use AppBundle\Entity\FeedSetting;
use AppBundle\Entity\User;
use AppBundle\Utilities\SlugUtilities;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class BranchService
 * @package AppBundle\Services
 */
class BranchService
{
    const DEFAULT_MINIMUM_REVIEW_VALUE = .8;

    private $em;
    private $authChecker;
    private $planService;
    private $endorsementResponseService;
    private $surveyService;
    private $feedService;

    /**
     * BranchService constructor.
     * @param EntityManager $em
     * @param AuthorizationChecker $authChecker
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManager $em,
        AuthorizationChecker $authChecker,
        ContainerInterface $container
    ) {
        $this->em = $em;
        $this->authChecker = $authChecker;
        $this->planService = $container->get('plan_service');
        $this->endorsementResponseService = $container->get('endorsement_response_service');
        $this->surveyService = $container->get('survey_service');
        $this->feedService = $container->get('feed_service');
    }

    /**
     * @param Branch $branch
     * @return Branch
     */
    public function createBranch(Branch $branch)
    {
        $br = new Branch;
        $br->setName($branch->getName());
        $br->setSlug($this->getBranchSlug($branch));
        $br->setAddress1($branch->getAddress1());
        $br->setAddress2($branch->getAddress2());
        $br->setCity($branch->getCity());
        $br->setState($branch->getState());
        $br->setZip($branch->getZip());
        $br->setIsRepReviewsClient(false);
        $br->setActive(true);

        if ($branch->getCompany() && null !== $branch->getCompany()->getId()) {
            $br->setCompany($this->em->getReference('AppBundle:Company', $branch->getCompany()->getId()));
        }

        $this->em->persist($br);
        $this->em->flush();

        return $br;
    }

    /**
     * @param Branch $br
     * @param Branch $branch
     * @return Branch
     */
    public function updateBranch(Branch $br, Branch $branch)
    {
        $br->setName($branch->getName());
        $br->setSlug($branch->getSlug());
        $br->setAddress1($branch->getAddress1());
        $br->setAddress2($branch->getAddress2());
        $br->setCity($branch->getCity());
        $br->setState($branch->getState());
        $br->setZip($branch->getZip());
        $br->setActive((empty($branch->getActive())) ? false: $branch->getActive());

        // When a company administrator updates payment preferences...
        if ($this->adjustPaymentScheme($branch)) {
            $plan = $this->em->getRepository('AppBundle:Stripe\Plan')->getPlanByStripeId($branch->getTargetPlan());
            $payee = $this->em->getRepository('AppBundle:User')->getUserByStripeId($branch->getPayee());
            $payee->setPlan($plan);

            $this->planService->updateBranchSubscriptionQuantity(
                $br,
                $payee
            );
        }

        $this->em->persist($br);
        $this->em->flush();

        return $br;
    }

    /**
     * @param Branch $branch
     * @return string
     */
    public function getBranchSlug(Branch $branch)
    {
        $slug = SlugUtilities::slugify($branch->getName());
        $slugIsUnique = false;
        $repo = $this->em->getRepository(Branch::class);

        while (!$slugIsUnique) {
            $query = $repo->isSlugUnique($slug);

            if (!$query) {
                $slug .= '-' . substr(md5(uniqid()), 0, 5);
            } else {
                $slugIsUnique = true;
            }
        }

        return $slug;
    }

    /**
     * @param Branch $branch
     * @return array|null
     */
    public function getBranchEndorsementFeed(Branch $branch, bool $includeVideosOnly = false)
    {
        $company = $branch->getCompany();

        $surveys = $this->surveyService->getSurveysByBranch($branch, null);

        if (!$surveys) {
            return [];
        }

        $feed = ($this->feedService->isFeedOverridden($company)) ?
            $this->em->getRepository(FeedSetting::class)->findOneBy(['company' => $company->getId()]) :
            $this->em->getRepository(FeedSetting::class)->findOneBy(['branch' => $branch->getId()]);

        $branchAdmin = $this->em->getRepository(User::class)->getBranchAdministrator($branch);

        return $this->endorsementResponseService->getEndorsementFeed(
            $surveys,
            ($feed) ? $feed->getMinimumReviewValue() : self::DEFAULT_MINIMUM_REVIEW_VALUE,
            $branchAdmin,
            $this->getReviewAggregationTokens($branch),
            null,
            false,
            $includeVideosOnly
        );
    }

    /**
     * @param Branch $branch
     * @return bool
     */
    private function adjustPaymentScheme(Branch $branch)
    {
        if (!$this->authChecker->isGranted('ROLE_BRANCH_ADMIN')) {
            return false;
        }

        if (true == $branch->getUpdatePaymentPlan() && null !== $branch->getTargetPlan()) {
            return true;
        }

        return false;
    }

    /**
     * @param Branch $branch
     * @return array
     */
    private function getReviewAggregationTokens(Branch $branch)
    {
        $arr = [];

        // If the user has no preferences, default to include review aggregation tokens.  Otherwise, use setting.
        if (!$branch->getPreferences() ||
            ($branch->getPreferences() && $branch->getPreferences()->getIncludeExternalReviewsInFeeds())
        ) {
            if (null !== $branch->getGoogleReviewAggregationToken()) {
                $arr[] = $branch->getGoogleReviewAggregationToken();
            }

            if (null !== $branch->getFacebookReviewAggregationToken()) {
                $arr[] = $branch->getFacebookReviewAggregationToken();
            }

            if (null !== $branch->getZillowNmlsidToken()) {
                $arr[] = $branch->getZillowNmlsidToken();
            }

            if (null !== $branch->getZillowScreenNameToken()) {
                $arr[] = $branch->getZillowScreenNameToken();
            }
        }

        return $arr;
    }
}
