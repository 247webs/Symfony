<?php

namespace AppBundle\BroadcastManagers;

use AppBundle\Document\Answer\CommentBoxAnswer;
use AppBundle\Document\EndorsementRequest;
use AppBundle\Document\EndorsementResponse;
use AppBundle\Document\Sharing\AutoSharing;
use AppBundle\Document\Sharing\Broadcaster\BroadcasterAbstract;
use AppBundle\Document\Sharing\SharingProfile;
use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\CompanySettings;
use AppBundle\Entity\SocialMediaBanner;
use AppBundle\Entity\Survey;
use AppBundle\Entity\User;
use AppBundle\Enumeration\FeatureAccessLevel;
use AppBundle\Model\Share;
use AppBundle\Repository\BranchProfileRepository;
use AppBundle\Repository\CompanyProfileRepository;
use AppBundle\Repository\UserProfileRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BroadcastManagerAbstract
 * @package AppBundle\BroadcastManagers
 */
abstract class BroadcastManagerAbstract
{
    /** @var EntityManager $em */
    protected $em;

    /** @var DocumentManager $dm */
    protected $dm;

    /** @var ContainerInterface $container */
    protected $container;

    /** @var \AppBundle\Repository\UserRepository|\Doctrine\ORM\EntityRepository $userRepo */
    protected $userRepo;

    /** @var \AppBundle\Repository\BranchRepository|\Doctrine\ORM\EntityRepository $branchRepo */
    protected $branchRepo;

    /** @var \AppBundle\Repository\CompanyRepository|\Doctrine\ORM\EntityRepository $companyRepo */
    protected $companyRepo;

    /** @var UserProfileRepository $userProfileRepo */
    protected $userProfileRepo;

    /** @var string $banner */
    protected $banner;

    /** @var BranchProfileRepository $branchProfileRepo */
    protected $branchProfileRepo;

    /** @var CompanyProfileRepository $companyProfileRepo */
    protected $companyProfileRepo;

    /** @var \AppBundle\Document\Sharing\AutoSharingRepository|\Doctrine\ODM\MongoDB\DocumentRepository
     * $autoSharingRepo
     */
    protected $autoSharingRepo;

    /** @var string $awsProfileImagePrefix */
    protected $awsProfileImagePrefix;

    /** @var float $minimumRating */
    protected $minimumRating = .8;

    /** @var string $awsResellerImagePrefix */
    protected $awsResellerImagePrefix;

    /** @var string $awsSocialMediaBannerPrefix */
    protected $awsSocialMediaBannerPrefix;

    /**
     * BroadcastManagerAbstract constructor.
     * @param EntityManager $em
     * @param DocumentManager $dm
     * @param ContainerInterface $container
     * @param string $banner
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        ContainerInterface $container,
        string $banner
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->container = $container;
        $this->userRepo = $this->em->getRepository(User::class);
        $this->branchRepo = $this->em->getRepository(Branch::class);
        $this->companyRepo = $this->em->getRepository(Company::class);
        $this->autoSharingRepo = $this->dm->getRepository(AutoSharing::class);
        $this->awsProfileImagePrefix = $this->container->getParameter('aws_cloudfront') . '/'
            . $this->container->getParameter('aws_public_profile_image_directory') . '/';
        $this->banner = $banner;
        $this->awsResellerImagePrefix = $this->container->getParameter('aws_cloudfront') . '/'
            . $this->container->getParameter('aws_reseller_logo_image_directory') . '/';
        $this->awsSocialMediaBannerPrefix = $this->container->getParameter('aws_cloudfront') . '/'
            . $this->container->getParameter('aws_social_media_banner_image_directory') . '/';
    }

    /**
     * @param array $shares
     * @return array
     */
    public function addEndorsementsToShares(array $shares, string $broadcaster) :array
    {
        /** @var Share $share */
        foreach ($shares as $key => $share) {
            $endorsements = $this->getEndorsements($share->getSharingProfile(), $broadcaster);

            if (empty($endorsements)) { // If we can't find an endorsement, remove the share.
                unset($shares[$key]);
            } else {
                $shares[$key] = $this->buildShare($share, null, null, $endorsements);
            }
        }

        return $shares;
    }

    /**
     * @param EndorsementResponse $endorsementResponse
     * @param string $response
     * @param string $broadcaster
     * @param string $type
     * @return bool
     */
    public function recordShare(
        EndorsementResponse $endorsementResponse,
        string $response,
        string $broadcaster,
        string $type
    ) {
        $date = new \DateTime;
        $shared = $endorsementResponse->getShared();

        switch ($type) {
            case 'company':
                $shared['company_' . $broadcaster ] = $date->format('Y-m-d H:i:s') . ' ' . $response;
                break;
            case 'branch':
                $shared['branch' . $broadcaster ] = $date->format('Y-m-d H:i:s') . ' ' . $response;
                break;
            default:
                $shared['user_' . $broadcaster ] = $date->format('Y-m-d H:i:s') . ' ' . $response;
        }

        $endorsementResponse->setShared($shared);

        $this->dm->persist($endorsementResponse);
        $this->dm->flush();

        return true;
    }

    /**
     * @return array
     */
    protected function getSharers() :array
    {
        $sharers = [];
        $now = new \DateTime;
        $windowStart = $this->getWindowStart($now);
        $windowEnd = $this->getWindowEnd($now);

        // Find sharers that have a) enabled auto sharing and b) scheduled it for the current day of the week.
        $qb = $this->autoSharingRepo->createQueryBuilder()
            ->field('enabled')->equals(true)
            ->field($this->getDayOfWeek($now))->notEqual(null)
            ->getQuery()
            ->execute();

        // If we have sharing candidates, get their share time
        if (count($qb)) {
            /** @var AutoSharing $sharer */
            foreach ($qb as $sharer) {
                // Create getter method
                $getterMethod = 'get' . $now->format('l');

                if (method_exists($sharer, $getterMethod)) {
                    /** @var \DateTime $sharerTime */
                    $sharerTime = $sharer->$getterMethod();

                    // Normalize the date
                    $sharerTime->setDate('2000', '01', '01');

                    // Represent the sharer time as an integer
                    $normalizedTime = strtotime($sharerTime->format('Y-m-d H:i:s'));

                    // If their share time falls between our upper and lower sharing windows, add them to sharers arr.
                    if ($normalizedTime >= $windowStart && $normalizedTime < $windowEnd) {
                        $sharers[] = $sharer;
                    }
                }
            }
        }

        return $sharers;
    }

    /**
     * @param AutoSharing $autoSharing
     * @return bool
     */
    protected function getEntityCanShare(AutoSharing $autoSharing) :bool
    {
        $canShare = false;
        /** @var SharingProfile $sharingProfile */
        $sharingProfile = $autoSharing->getSharingProfile();

        if ($sharingProfile) {
            if (null !== $sharingProfile->getUserId()) {
                $user = $this->userRepo->find($sharingProfile->getUserId());

                // If we find a user, that user is active and not on a free plan, they can share
                if ($user && $user->getActive() && $user->getPlan()->getAcl() != FeatureAccessLevel::FREE) {
                    $canShare = true;
                }
            }

            if (null !== $sharingProfile->getBranchId()) {
                $branch = $this->branchRepo->find($sharingProfile->getBranchId());

                /** @todo update so that we're keying off the branch admin's plan? */
                if ($branch && $branch->getActive()) {
                    $canShare = true;
                }
            }

            if (null !== $sharingProfile->getCompanyId()) {
                $company = $this->companyRepo->find($sharingProfile->getCompanyId());

                /** @todo update so that we're keying off the company admin's plan? */
                if ($company && $company->getActive()) {
                    $canShare = true;
                }
            }
        }

        return $canShare;
    }

    /**
     * @param Share|null $share
     * @param BroadcasterAbstract|null $broadcaster
     * @param SharingProfile|null $sharingProfile
     * @param array|null $endorsements
     * @return Share
     */
    protected function buildShare(
        Share $share = null,
        BroadcasterAbstract $broadcaster = null,
        SharingProfile $sharingProfile = null,
        array $endorsements = null
    ) :Share {
        $share = (null === $share) ? new Share : $share;

        if (null !== $broadcaster) {
            $share->setBroadcaster($broadcaster);
        }

        if (null !== $sharingProfile) {
            $share->setSharingProfile($sharingProfile);
        }

        if (null !== $endorsements) {
            $share->setEndorsements($endorsements);
        }

        if (null !== $sharingProfile) {
            if (null !== $sharingProfile->getUserId()) {
                $user = $this->userRepo->find($sharingProfile->getUserId());

                if ($user) {
                    $share->setUser($user);
                }
            }

            if (null !== $sharingProfile->getBranchId()) {
                $branch = $this->branchRepo->find($sharingProfile->getBranchId());

                if ($branch) {
                    $share->setBranch($branch);
                }
            }

            if (null !== $sharingProfile->getCompanyId()) {
                $company = $this->companyRepo->find($sharingProfile->getCompanyId());

                if ($company) {
                    $share->setCompany($company);
                }
            }
        }

        return $share;
    }

    /**
     * @param SharingProfile $sharingProfile
     * @return array
     */
    protected function getEndorsements(SharingProfile $sharingProfile, string $broadcaster)
    {
        if (null !== $sharingProfile->getCompanyId()) {
            return $this->getCompanyEndorsements($sharingProfile->getCompanyId(), $broadcaster);
        }

        if (null !== $sharingProfile->getBranchId()) {
            return $this->getBranchEndorsements($sharingProfile->getBranchId(), $broadcaster);
        }

        if (null !== $sharingProfile->getUserId()) {
            return $this->getUserEndorsements($sharingProfile->getUserId(), $broadcaster);
        }

        return [];
    }

    /**
     * @param Share $share
     * @return string
     */
    protected function getShareType(Share $share) :string
    {
        if ($share->getCompany()) {
            return 'company';
        }

        if ($share->getBranch()) {
            return 'branch';
        }

        return 'user';
    }

    /**
     * @param EndorsementResponse $endorsementResponse
     * @return mixed|null
     */
    public function getEndorsementComments(EndorsementResponse $endorsementResponse)
    {
        $answers = $endorsementResponse->getAnswers();

        foreach ($answers as $answer) {
            if ($answer instanceof CommentBoxAnswer &&
                CommentBoxAnswer::ENDORSEMENT_TYPE === $answer->getCommentBoxAnswerType()
            ) {
                return $answer->getAnswer();
            }
        }

        return null;
    }

    /**
     * @param User $user
     * @param string $type
     * @return bool|string
     */
    protected function getCustomBanner(User $user, string $type)
    {
        /** @var CompanySettings $companySettings
         *  Check for company settings for this user
         */
        $companySettings = $this->getCompanySettings($user);

        /** If company has settings and has opted to suppress social images,
         *  check to see if company admin has uploaded a custom banner for the
         *  broadcaster type specified.  If so, return the banner string.
         *  Otherwise, proceed.
         */
        if ($companySettings && $companySettings->getSuppressSocialImages()) {
            if ($companyBanner = $this->getCompanyBanner($companySettings, $type)) {
                return $companyBanner;
            }
        }

        /** If user has overridden default banner, return the banner */
        if ($user->getSocialMediaBanners()) {
            /** @var SocialMediaBanner $socialMediaBanner */
            foreach ($user->getSocialMediaBanners() as $socialMediaBanner) {
                if ($socialMediaBanner->getType() === $type) {
                    return $this->awsSocialMediaBannerPrefix . $socialMediaBanner->getBanner();
                }
            }
        }

        /** Otherwise, custom banners are not being used  */
        return false;
    }

    /**
     * @param \DateTime $now
     * @return string
     */
    private function getDayOfWeek(\DateTime $now)
    {
        return strtolower($now->format('l'));
    }

    /**
     * @param \DateTime $now
     * @return false|int
     */
    private function getWindowStart(\DateTime $now)
    {
        // Normalize the date
        $datetime = new \DateTime('2000-01-01 ' . $now->format('H:i:s'));

        // Round down to previous half hour
        $datetime->setTime($now->format('H'), ($i = $datetime->format('i')) - ($i % 30), 0);

        // Return an integer representing the earliest time we want to accept
        return strtotime($datetime->format('Y-m-d H:i:s'));
    }

    /**
     * @param \DateTime $now
     * @return false|int
     */
    private function getWindowEnd(\DateTime $now)
    {
        // Normalize the date
        $datetime = new \DateTime('2000-01-01 ' . $now->format('H:i:s'));
        $i = $datetime->format('i') >= 30 ? 00 : 30;
        $h = ($i === 00) ? $now->modify('+1 hour') : $now;

        // Round up to the next half hour
        $datetime->setTime($h->format('H'), $i, 0);

        // Return an integer representing the earliest time we want to accept
        return strtotime($datetime->format('Y-m-d H:i:s'));
    }

    /**
     * @param int $id
     * @return array
     */
    private function getUserEndorsements(int $id, string $broadcaster)
    {
        $endorsements = [];
        $user = $this->userRepo->find($id);

        if ($user) {
            $surveys = $this->container->get('survey_service')->getSurveysByUser($user, true);

            if ($surveys) {
                $surveyIds = [];

                /** @var Survey $survey */
                foreach ($surveys as $survey) {
                    $surveyIds[] = $survey->getId();
                }

                $endorsementRequests = $this->getEndorsementRequests($surveyIds);

                if ($endorsementRequests) {
                    $endorsementRequestIds = [];

                    /** @var EndorsementRequest $endorsementRequest */
                    foreach ($endorsementRequests as $endorsementRequest) {
                        $endorsementRequestIds[] = $endorsementRequest->getId();
                    }

                    $endorsementResponse = $this->dm->getRepository('AppBundle:EndorsementResponse')
                        ->createQueryBuilder()
                        ->field('endorsement_request.id')->in($endorsementRequestIds)
                        ->field('can_share')->equals(true)
                        ->field('status')->equals('active')
                        ->field('rating')->gte($this->minimumRating)
                        ->field('shared.user_' . $broadcaster)->exists(false)
                        ->field('submitted')->gte(new \DateTime('2017-02-12 00:00:00')) // Date v2 went live
                        ->field('submitted')->lte(new \DateTime('-2 days')) // Must be at least 2 days old
                        ->getQuery()
                        ->getSingleResult();

                    if ($endorsementResponse) {
                        $endorsements[] = $endorsementResponse;
                    }
                }
            }
        }

        return $endorsements;
    }

    /**
     * @param int $id
     * @return array
     */
    private function getBranchEndorsements(int $id, string $broadcaster)
    {
        $endorsements = [];
        $branch = $this->branchRepo->find($id);

        if ($branch) {
            $surveys = $this->container->get('survey_service')->getSurveysByBranch($branch, true);

            if ($surveys) {
                $surveyIds = [];

                /** @var Survey $survey */
                foreach ($surveys as $survey) {
                    $surveyIds[] = $survey->getId();
                }

                $endorsementRequests = $this->getEndorsementRequests($surveyIds);

                if ($endorsementRequests) {
                    $endorsementRequestIds = [];

                    /** @var EndorsementRequest $endorsementRequest */
                    foreach ($endorsementRequests as $endorsementRequest) {
                        $endorsementRequestIds[] = $endorsementRequest->getId();
                    }

                    $endorsementResponse = $this->dm->getRepository('AppBundle:EndorsementResponse')
                        ->createQueryBuilder()
                        ->field('endorsement_request.id')->in($endorsementRequestIds)
                        ->field('can_share')->equals(true)
                        ->field('status')->equals('active')
                        ->field('rating')->gte($this->minimumRating)
                        ->field('shared.branch_' . $broadcaster)->exists(false)
                        ->field('submitted')->gte(new \DateTime('2017-02-12 00:00:00')) // Date v2 went live
                        ->field('submitted')->lte(new \DateTime('-2 days')) // Must be at least 2 days old
                        ->getQuery()
                        ->getSingleResult();

                    if ($endorsementResponse) {
                        $endorsements[] = $endorsementResponse;
                    }
                }
            }
        }

        return $endorsements;
    }

    /**
     * @param int $id
     * @return array
     */
    private function getCompanyEndorsements(int $id, string $broadcaster)
    {
        $endorsements = [];
        $company = $this->companyRepo->find($id);

        if ($company) {
            $surveys = $this->container->get('survey_service')->getSurveysByCompany($company, true);

            if ($surveys) {
                $surveyIds = [];

                /** @var Survey $survey */
                foreach ($surveys as $survey) {
                    $surveyIds[] = $survey->getId();
                }

                $endorsementRequests = $this->getEndorsementRequests($surveyIds);

                if ($endorsementRequests) {
                    $endorsementRequestIds = [];

                    /** @var EndorsementRequest $endorsementRequest */
                    foreach ($endorsementRequests as $endorsementRequest) {
                        $endorsementRequestIds[] = $endorsementRequest->getId();
                    }

                    $endorsementResponse = $this->dm->getRepository('AppBundle:EndorsementResponse')
                        ->createQueryBuilder()
                        ->field('endorsement_request.id')->in($endorsementRequestIds)
                        ->field('can_share')->equals(true)
                        ->field('status')->equals('active')
                        ->field('rating')->gte($this->minimumRating)
                        ->field('shared.company_' . $broadcaster)->exists(false)
                        ->field('submitted')->gte(new \DateTime('2017-02-12 00:00:00')) // Date v2 went live
                        ->field('submitted')->lte(new \DateTime('-2 days')) // Must be at least 2 days old
                        ->getQuery()
                        ->getSingleResult();

                    if ($endorsementResponse) {
                        $endorsements[] = $endorsementResponse;
                    }
                }
            }
        }

        return $endorsements;
    }

    /**
     * @param array $surveyIds
     * @return mixed
     */
    private function getEndorsementRequests(array $surveyIds)
    {
        return $this->dm->getRepository('AppBundle:EndorsementRequest')
            ->createQueryBuilder()
            ->field('survey_id')->in($surveyIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param User $user
     * @return CompanySettings|null
     */
    private function getCompanySettings(User $user)
    {
        // Make sure that the user has a branch and a company
        if (!$user->getBranch() || !$user->getBranch()->getCompany()) {
            return null;
        }

        return $user->getBranch()->getCompany()->getSettings();
    }

    /**
     * @param CompanySettings $companySettings
     * @param string $type
     * @return bool|string
     */
    private function getCompanyBanner(CompanySettings $companySettings, string $type)
    {
        /** @var User $companyAdmin */
        $companyAdmin = $this->userRepo->getCompanyAdministrator($companySettings->getCompany());

        if (!$companyAdmin) {
            return false;
        }

        if ($companyAdmin->getSocialMediaBanners()) {
            /** @var SocialMediaBanner $socialMediaBanner */
            foreach ($companyAdmin->getSocialMediaBanners() as $socialMediaBanner) {
                if ($socialMediaBanner->getType() === $type) {
                    return $this->awsSocialMediaBannerPrefix . $socialMediaBanner->getBanner();
                }
            }
        }

        return false;
    }
}
