<?php

namespace AppBundle\Services;

use AppBundle\Document\EndorsementRequest;
use AppBundle\Document\EndorsementResponse;
use AppBundle\Document\Sharing\VerificationNoticePriority;
use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\QuestionType\CommentBox;
use AppBundle\Entity\QuestionType\StarRating;
use AppBundle\Entity\Survey;
use AppBundle\Entity\SurveyPushUrl;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\User;
use AppBundle\Entity\Wix\WixUser;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;

/**
 * Class SurveyService
 * @package AppBundle\Services
 */
class SurveyService
{
    /** @var EntityManager $em */
    private $em;

    /** @var DocumentManager $dm */
    private $dm;

    /** @var string $frontEndUiUrl */
    private $frontEndUiUrl;

    /** @var string $frontEndSurveyUri */
    private $frontEndSurveyUri;

    /** @var string $frontEndFeedbackUri */
    private $frontEndFeedbackUri;

    /**
     * SurveyService constructor.
     * @param EntityManager $em
     * @param DocumentManager $dm
     * @param string $frontEndUiUrl
     * @param string $frontEndSurveyUri
     */
    public function __construct(
        EntityManager $em,
        DocumentManager $dm,
        string $frontEndUiUrl,
        string $frontEndSurveyUri,
        string $frontEndFeedbackUri
    ) {
        $this->em = $em;
        $this->dm = $dm;
        $this->frontEndUiUrl = $frontEndUiUrl;
        $this->frontEndSurveyUri = $frontEndSurveyUri;
        $this->frontEndFeedbackUri = $frontEndFeedbackUri;
    }

    /**
     * @param Survey $survey
     * @param User|null $user
     * @param WixUser|null $wixUser
     * @return Survey
     */
    public function createSurvey(Survey $survey, User $user = null, WixUser $wixUser = null)
    {
        $s = new Survey();
        (!$user instanceof User) ?: $s->setUser($user);
        (!$wixUser instanceof WixUser) ?: $s->setWixUser($wixUser);
        $s->setSurveyName($survey->getSurveyName());
        $s->setSurveySubjectLine($survey->getSurveySubjectLine());
        $s->setSurveyGreeting($survey->getSurveyGreeting());
        $s->setSurveyMessage($survey->getSurveyMessage());
        $s->setSurveyLinkLabel($survey->getSurveyLinkLabel());
        $s->setSurveyLinkColor($survey->getSurveyLinkColor());
        $s->setSurveyLinkTextColor($survey->getSurveyLinkTextColor());
        $s->setIgnoreButtonText((empty($survey->getIgnoreButtonText())) ? null : $survey->getIgnoreButtonText());
        $s->setSurveySignOff($survey->getSurveySignOff());
        $s->setMerchantFirstName($survey->getMerchantFirstName());
        $s->setMerchantLastName((empty($survey->getMerchantLastName())) ? null : $survey->getMerchantLastName());
        $s->setMerchantTitle((empty($survey->getMerchantTitle())) ? null : $survey->getMerchantTitle());
        $s->setMerchantEmailAddress($survey->getMerchantEmailAddress());
        $s->setMerchantPhone((empty($survey->getMerchantPhone())) ? null : $survey->getMerchantPhone());
        $s->setActive(true);
        $s->setIsDefault((true === $survey->getIsDefault()) ? true : false);
        $s->setType(strtolower($survey->getType()));
        $s->setIsGlobalSurvey(false);

        if (count($survey->getPushUrls())) {
            /** @var SurveyPushUrl $pushUrl */
            foreach ($survey->getPushUrls() as $pushUrl) {
                $pushUrl->setSurvey($s);
                $s->addPushUrl($pushUrl);
            }
        }

        $this->em->persist($s);

        if (count($survey->getSurveyQuestions())) {
            $s->setSurveyQuestions($survey->getSurveyQuestions());
        }

        $this->em->flush();

        $this->manageDefaultSurvey($s);

        return $s;
    }

    /**
     * @param User $user
     * @return Survey
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createDefaultSurvey(User $user)
    {
        $s = new Survey;
        $s->setUser($user);
        $s->setSurveyName('Share Your Experience');
        $s->setSurveySubjectLine('Share Your Experience');
        $s->setSurveyGreeting('Hello');
        $s->setSurveyMessage('We appreciate your business. Your review is extremely valuable and this link makes it ' .
            'really easy. If you would please take just a moment I would sincerely appreciate it.');
        $s->setSurveyLinkLabel('Survey');
        $s->setSurveyLinkColor('#0883B8');
        $s->setSurveyLinkTextColor('#FFFFFF');
        $s->setIgnoreButtonText(null);
        $s->setSurveySignOff('Thanks again');
        $s->setMerchantFirstName($user->getFirstName());
        $s->setMerchantLastName($user->getLastName());
        $s->setMerchantEmailAddress($user->getUsername());
        $s->setActive(true);
        $s->setIsDefault(true);
        $s->setType(Survey::SURVEY_TYPE_BASIC);
        $s->setIsGlobalSurvey(false);

        $survey = $this->save($s);

        $q1 = new StarRating;
        $q1->setQuestion('Rating');
        $q1->setPrompt('Rating');
        $q1->setIsRequired(true);
        $q1->setPosition(1);
        $q1->setScale(5);
        $q1->setShape(StarRating::STAR_SHAPE);
        $q1->setSurvey($survey);

        $this->em->persist($q1);

        $q2 = new CommentBox;
        $q2->setCommentBoxType(CommentBox::ENDORSEMENT_TYPE);
        $q2->setQuestion('Comments');
        $q2->setPrompt('Comments');
        $q2->setIsRequired(true);
        $q2->setPosition(2);
        $q2->setSurvey($survey);

        $this->em->persist($q2);

        $this->em->flush();

        $this->applyGlobalSurvey($user);

        return $survey;
    }

    public function copySurveyToAllUsers(Survey $survey, bool $setAsDefault)
    {
        $admin = $survey->getUser();
        $company = $survey->getUser()->getBranch()->getCompany();
        $userRepo = $this->em->getRepository(User::class);

        $qb = $userRepo->createQueryBuilder('user');

        $users = $qb
            ->join('user.branch', 'branch')
            ->join('branch.company', 'company')
            ->where($qb->expr()->neq('user', ':admin'))
            ->andWhere($qb->expr()->eq('company', ':company'))
            ->setParameter('admin', $admin)
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();

        /** @var User $user */
        foreach ($users as $user) {
            $s = $this->copySurveyToUser($survey, $user, $setAsDefault);
            $this->manageDefaultSurvey($s);
        }
    }

    /**
     * @param Survey $s
     * @param Survey $survey
     * @return Survey
     */
    public function updateSurvey(Survey $s, Survey $survey)
    {
        $s->setSurveyName($survey->getSurveyName());
        $s->setSurveySubjectLine($survey->getSurveySubjectLine());
        $s->setSurveyGreeting($survey->getSurveyGreeting());
        $s->setSurveyMessage($survey->getSurveyMessage());
        $s->setSurveyLinkLabel($survey->getSurveyLinkLabel());
        $s->setSurveyLinkColor($survey->getSurveyLinkColor());
        $s->setSurveyLinkTextColor($survey->getSurveyLinkTextColor());
        $s->setIgnoreButtonText((empty($survey->getIgnoreButtonText())) ? null : $survey->getIgnoreButtonText());
        $s->setSurveySignOff($survey->getSurveySignOff());
        $s->setMerchantFirstName($survey->getMerchantFirstName());
        $s->setMerchantLastName((empty($survey->getMerchantLastName())) ? null : $survey->getMerchantLastName());
        $s->setMerchantTitle((empty($survey->getMerchantTitle())) ? null : $survey->getMerchantTitle());
        $s->setMerchantEmailAddress($survey->getMerchantEmailAddress());
        $s->setMerchantPhone((empty($survey->getMerchantPhone())) ? null : $survey->getMerchantPhone());
        $s->setActive((empty($survey->getActive())) ? false : $survey->getActive());
        if ($s->getType() !== Survey::SURVEY_TYPE_REVIEW_PUSH) {
            $s->setIsDefault((true === $survey->getIsDefault()) ? true : false);
        }

        if (count($s->getPushUrls())) {
            foreach ($s->getPushUrls() as $pushUrl) {
                $this->em->remove($pushUrl);
                $this->em->flush();
            }
        }

        /** @var SurveyPushUrl $pushUrl */
        if (count($survey->getPushUrls())) {
            foreach ($survey->getPushUrls() as $pushUrl) {
                $pushUrl->setSurvey($s);
                $s->addPushUrl($pushUrl);
            }
        }

        $this->save($s);

        $this->manageDefaultSurvey($s);

        return $s;
    }

    /**
     * @param Survey $survey
     * @return Survey
     */
    public function save(Survey $survey)
    {
        $this->em->persist($survey);
        $this->em->flush();

        return $survey;
    }

    /**
     * @param Company $company
     * @param bool $active
     * @return mixed|null
     */
    public function getSurveysByCompany(Company $company, $active = true)
    {
        $usersRepo = $this->em->getRepository(User::class);
        $users = $usersRepo->getUsersByCompany($company);

        if (!count($users)) {
            return []; // Return empty array means no survey found (because branch does not 
                       // have any user associated with it)
        }

        $ids = [];
        /** @var User $user */
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }

        $surveyRepo = $this->em->getRepository(Survey::class);
        $qb = $surveyRepo->createQueryBuilder('s');
        $qb
            ->join('s.user', 'user')
            ->where('user.id IN (:ids)')
            ->setParameter('ids', $ids);

        if (null !== $active) {
            $qb
                ->andWhere('s.active = :active')
                ->setParameter('active', $active);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Branch $branch
     * @param bool $active
     * @return mixed|null
     */
    public function getSurveysByBranch(Branch $branch, $active = true)
    {
        $usersRepo = $this->em->getRepository(User::class);
        $users = $usersRepo->getUsersByBranch($branch);

        if (!count($users)) {
            return []; // Return empty array means no survey found (because branch does not 
                       // have any user associated with it)
        }

        $ids = [];
        /** @var User $user */
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }

        $surveyRepo = $this->em->getRepository(Survey::class);
        $qb = $surveyRepo->createQueryBuilder('s');
        $qb
            ->join('s.user', 'user')
            ->where('user.id IN (:ids)')
            ->setParameter('ids', $ids);

        if (null !== $active) {
            $qb
                ->andWhere('s.active = :active')
                ->setParameter('active', $active);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param bool $active
     * @param array|null $survey_types
     * @return mixed
     */
    public function getSurveysByUser(User $user, $active = true, $survey_types = null)
    {
        $surveyRepo = $this->em->getRepository(Survey::class);
        $qb = $surveyRepo->createQueryBuilder('s')
            ->join('s.user', 'user')
            ->where('user.id = :userId')
            ->setParameter('userId', $user->getId());

        if (null !== $active) {
            $qb
                ->andWhere('s.active = :active')
                ->setParameter('active', $active);
        }

        if (null !== $survey_types) {
            $qb
                ->andWhere('s.type IN (:survey_types)')
                ->setParameter('survey_types', $survey_types);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Survey $survey
     * @return mixed|null
     */
    public function getEndorsementResponsesBySurvey(Survey $survey)
    {
        $endorsementRequestRepo = $this->dm->getRepository(EndorsementRequest::class);
        $endorsementResponseRepo = $this->dm->getRepository(EndorsementResponse::class);

        $endorsementRequests = $endorsementRequestRepo->getEndorsementRequestsBySurvey($survey);

        if (!count($endorsementRequests)) {
            return null;
        }

        /** @var array $endorsementRequestIds */
        $endorsementRequestIds = [];

        /** @var EndorsementRequest $endorsementRequest */
        foreach ($endorsementRequests as $endorsementRequest) {
            $endorsementRequestIds[] = $endorsementRequest->getId();
        }

        return $endorsementResponseRepo->getEndorsementsByRequestIds($endorsementRequestIds);
    }

    /**
     * @param Survey $survey
     * @param EndorsementRequest|null $endorsementRequest
     * @return string
     */
    public function getSurveyLink(Survey $survey, EndorsementRequest $endorsementRequest = null)
    {
        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                return $this->getReviewPushSurveyLink($survey);
            case Survey::SURVEY_TYPE_VIDEOMONIAL:
                return $this->getVideomonialSurveyLink($survey, $endorsementRequest);
            default:
                return $this->getNonReviewPushSurveyLink($survey, $endorsementRequest);
        }
    }

    /**
     * @param Survey $survey
     * @param EndorsementRequest|null $endorsementRequest
     * @return string
     */
    public function getFeedbackLink(Survey $survey, EndorsementRequest $endorsementRequest = null)
    {
        $link = $this->frontEndUiUrl . $this->frontEndFeedbackUri . '/' . base64_encode($survey->getId());

        if (null !== $endorsementRequest) {
            $link .= '?endorsement_request=' . base64_encode($endorsementRequest->getId()) . '&';
            $link .= 'email=' . base64_encode($endorsementRequest->getRecipientEmail()) . '&';

            if (null !== $endorsementRequest->getRecipientFirstName()) {
                $link .= 'first=' . base64_encode($endorsementRequest->getRecipientFirstName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLastName()) {
                $link .= 'last=' . base64_encode($endorsementRequest->getRecipientLastName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientCity()) {
                $link .= 'city=' . base64_encode($endorsementRequest->getRecipientCity()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientState()) {
                $link .= 'state=' . base64_encode($endorsementRequest->getRecipientState()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLabel()) {
                $link .= 'label=' . base64_encode($endorsementRequest->getRecipientLabel());
            }
        }

        return $link;
    }

    /**
     * @param Survey $survey
     */
    public function manageDefaultSurvey(Survey $survey)
    {
        $usersSurveys = $this->getSurveysByUser($survey->getUser(), null);

        // If this survey is the default survey, all other surveys owned by this user become not default
        if ($survey->getIsDefault()) {
            /** @var Survey $usersSurvey */
            foreach ($usersSurveys as $usersSurvey) {
                if ($usersSurvey->getId() !== $survey->getId()) {
                    $usersSurvey->setIsDefault(false);
                    $this->em->persist($usersSurvey);
                }
            }

            $this->em->flush();
        }

        // If this is not the user's default survey, make sure the owner has a default; otherwise, this one is it!
        if (!$survey->getIsDefault()) {
            $hasDefault = false;

            /** @var Survey $usersSurvey */
            foreach ($usersSurveys as $usersSurvey) {
                if ($usersSurvey->getIsDefault()) {
                    $hasDefault = true;
                }
            }

            if (!$hasDefault) {
                $survey->setIsDefault(true);
                $this->em->persist($survey);
                $this->em->flush();
            }
        }
    }

    /**
     * @param User $user
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function applyGlobalSurvey(User $user)
    {
        if ($user->getBranch() && $user->getBranch()->getCompany()) {
            $globalSurvey = $this->getGlobalSurvey($user->getBranch()->getCompany());

            if ($globalSurvey instanceof Survey) {
                $survey = $this->copySurveyToUser($globalSurvey, $user);

                $this->manageDefaultSurvey($survey);
            }
        }
    }

    /**
     * @param Survey $survey
     * @param User $user
     * @param bool $isDefault
     * @return Survey
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function copySurveyToUser(Survey $survey, User $user, bool $isDefault = false)
    {
        $s = clone($survey);
        $s->setUser($user);
        $s->setMerchantFirstName($user->getFirstName());
        $s->setMerchantLastName($user->getLastName());
        $s->setMerchantEmailAddress($user->getUsername());
        $s->setActive(true);
        $s->setIsDefault($isDefault);
        $s->setIsGlobalSurvey(false);

        $s = $this->save($s);

        /** @var SurveyQuestion $surveyQuestion */
        foreach ($survey->getSurveyQuestions() as $surveyQuestion) {
            $copy = clone($surveyQuestion);
            $copy->setSurvey($s);
            $this->em->persist($copy);
        }

        $this->em->flush();

        return $s;
    }

    /**
     * @param Company $company
     * @return Survey|bool|null|object
     */
    private function getGlobalSurvey(Company $company)
    {
        if ($company->getSettings() && $company->getSettings()->getEnableGlobalSurvey()) {
            $companyAdmin = $this->em->getRepository(User::class)->getCompanyAdministrator($company);

            if ($companyAdmin) {
                $globalSurvey = $this->em->getRepository(Survey::class)->findOneBy([
                    'user' => $companyAdmin,
                    'isGlobalSurvey' => true,
                ]);

                return ($globalSurvey) ? $globalSurvey : false;
            }
        }

        return false;
    }

    /**
     * @param Survey $survey
     * @return string
     * Returns the survey push URL objects, future proofed so we can add additional push URLs to a survey
     */
    private function getReviewPushSurveyLink(Survey $survey)
    {
        return $survey->getPushUrls();
    }

    /**
     * @param Survey $survey
     * @param EndorsementRequest|null $endorsementRequest
     * @return string
     */
    private function getNonReviewPushSurveyLink(Survey $survey, EndorsementRequest $endorsementRequest = null)
    {
        $link = $this->frontEndUiUrl . $this->frontEndSurveyUri . '/' . base64_encode($survey->getId());

        if (null !== $endorsementRequest) {
            $link .= '?endorsement_request=' . base64_encode($endorsementRequest->getId()) . '&';
            $link .= 'email=' . base64_encode($endorsementRequest->getRecipientEmail()) . '&';

            if (null !== $endorsementRequest->getRecipientFirstName()) {
                $link .= 'first=' . base64_encode($endorsementRequest->getRecipientFirstName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLastName()) {
                $link .= 'last=' . base64_encode($endorsementRequest->getRecipientLastName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientCity()) {
                $link .= 'city=' . base64_encode($endorsementRequest->getRecipientCity()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientState()) {
                $link .= 'state=' . base64_encode($endorsementRequest->getRecipientState()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLabel()) {
                $link .= 'label=' . base64_encode($endorsementRequest->getRecipientLabel());
            }
        }

        return $link;
    }
    
    /**
     * @param Survey $survey
     * @param EndorsementRequest|null $endorsementRequest
     * @return string
     */
    private function getVideomonialSurveyLink(Survey $survey, EndorsementRequest $endorsementRequest = null)
    {
        $link = $this->frontEndUiUrl . $this->frontEndSurveyUri . '/' . base64_encode($survey->getId());

        if (null !== $endorsementRequest) {
            $link .= '?endorsement_request=' . base64_encode($endorsementRequest->getId()) . '&';
            $link .= 'email=' . base64_encode($endorsementRequest->getRecipientEmail()) . '&';

            if (null !== $endorsementRequest->getRecipientFirstName()) {
                $link .= 'first=' . base64_encode($endorsementRequest->getRecipientFirstName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLastName()) {
                $link .= 'last=' . base64_encode($endorsementRequest->getRecipientLastName()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientCity()) {
                $link .= 'city=' . base64_encode($endorsementRequest->getRecipientCity()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientState()) {
                $link .= 'state=' . base64_encode($endorsementRequest->getRecipientState()) . '&';
            }

            if (null !== $endorsementRequest->getRecipientLabel()) {
                $link .= 'label=' . base64_encode($endorsementRequest->getRecipientLabel());
            }
        }

        return $link;
    }
}
