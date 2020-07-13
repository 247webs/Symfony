<?php

namespace AppBundle\Report;

use AppBundle\Document\Statistic\BranchProfilePageView;
use AppBundle\Document\Statistic\BranchProfileSurveyLinkClick;
use AppBundle\Document\Statistic\CompanyProfilePageView;
use AppBundle\Document\Statistic\CompanyProfileSurveyLinkClick;
use AppBundle\Document\Statistic\EndorsementRequestDelivered;
use AppBundle\Document\Statistic\EndorsementRequestFailed;
use AppBundle\Document\Statistic\EndorsementRequestLinkClicked;
use AppBundle\Document\Statistic\EndorsementRequestOpened;
use AppBundle\Document\Statistic\EndorsementRequestSent;
use AppBundle\Document\Statistic\EndorsementResponseReceived;
use AppBundle\Document\Statistic\Share;
use AppBundle\Document\Statistic\UserProfilePageView;
use AppBundle\Document\Statistic\UserProfileSurveyLinkClick;
use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use AppBundle\Services\StatisticService;

class StatisticsReporter
{
    /**
     * @var StatisticService
     */
    private $statService;

    /**
     * StatisticsReporter constructor.
     * @param StatisticService $statisticService
     */
    public function __construct(StatisticService $statisticService)
    {
        $this->statService = $statisticService;
    }

    /**
     * @param User $user
     * @return array
     */
    public function reportByUser(User $user)
    {
        return [
            'endorsement_requests_sent' => $this->statService->countUserStats(
                $user,
                EndorsementRequestSent::class
            ),
            'endorsement_requests_delivered' => $this->statService->countUserStats(
                $user,
                EndorsementRequestDelivered::class
            ),
            'endorsement_requests_failed' => $this->statService->countUserStats(
                $user,
                EndorsementRequestFailed::class
            ),
            'endorsement_requests_opened' => $this->statService->countUserStats(
                $user,
                EndorsementRequestOpened::class
            ),
            'endorsement_request_links_clicked' => $this->statService->countUserStats(
                $user,
                EndorsementRequestLinkClicked::class
            ),
            'endorsement_responses_received' => $this->statService->countUserStats(
                $user,
                EndorsementResponseReceived::class
            ),
            'shares' => $this->statService->countUserStats(
                $user,
                Share::class
            ),
            'user_profile_views' => $this->statService->countUserStats(
                $user,
                UserProfilePageView::class
            ),
            'user_profile_survey_links_clicked' => $this->statService->countUserStats(
                $user,
                UserProfileSurveyLinkClick::class
            ),
            'average_score' => $this->statService->calculateAverageScoreByUser($user),
            'total_endorsements' => $this->statService->countScorableEndorsementsByUser($user),
            'average_promoter_index' => $this->statService->calculateAveragePromoterIndexByUser($user)
        ];
    }

    /**
     * @param Branch $branch
     * @return array
     */
    public function reportByBranch(Branch $branch)
    {
        return [
            'endorsement_requests_sent' => $this->statService->countBranchStats(
                $branch,
                EndorsementRequestSent::class
            ),
            'endorsement_requests_delivered' => $this->statService->countBranchStats(
                $branch,
                EndorsementRequestDelivered::class
            ),
            'endorsement_requests_failed' => $this->statService->countBranchStats(
                $branch,
                EndorsementRequestFailed::class
            ),
            'endorsement_requests_opened' => $this->statService->countBranchStats(
                $branch,
                EndorsementRequestOpened::class
            ),
            'endorsement_request_links_clicked' => $this->statService->countBranchStats(
                $branch,
                EndorsementRequestLinkClicked::class
            ),
            'endorsement_responses_received' => $this->statService->countBranchStats(
                $branch,
                EndorsementResponseReceived::class
            ),
            'shares' => $this->statService->countBranchStats(
                $branch,
                Share::class
            ),
            'branch_profile_views' => $this->statService->countBranchStats(
                $branch,
                BranchProfilePageView::class
            ),
            'branch_profile_survey_links_clicked' => $this->statService->countBranchStats(
                $branch,
                BranchProfileSurveyLinkClick::class
            ),
            'average_score' => $this->statService->calculateAverageScoreByBranch($branch),
            'total_endorsements' => $this->statService->countScorableEndorsementsByBranch($branch),
            'average_promoter_index' => $this->statService->calculateAveragePromoterIndexByBranch($branch)
        ];
    }

    /**
     * @param Company $company
     * @return array
     */
    public function reportByCompany(Company $company)
    {
        return [
            'endorsement_requests_sent' => $this->statService->countCompanyStats(
                $company,
                EndorsementRequestSent::class
            ),
            'endorsement_requests_delivered' => $this->statService->countCompanyStats(
                $company,
                EndorsementRequestDelivered::class
            ),
            'endorsement_requests_failed' => $this->statService->countCompanyStats(
                $company,
                EndorsementRequestFailed::class
            ),
            'endorsement_requests_opened' => $this->statService->countCompanyStats(
                $company,
                EndorsementRequestOpened::class
            ),
            'endorsement_request_links_clicked' => $this->statService->countCompanyStats(
                $company,
                EndorsementRequestLinkClicked::class
            ),
            'endorsement_responses_received' => $this->statService->countCompanyStats(
                $company,
                EndorsementResponseReceived::class
            ),
            'shares' => $this->statService->countCompanyStats(
                $company,
                Share::class
            ),
            'company_profile_views' => $this->statService->countCompanyStats(
                $company,
                CompanyProfilePageView::class
            ),
            'company_profile_survey_links_clicked' => $this->statService->countCompanyStats(
                $company,
                CompanyProfileSurveyLinkClick::class
            ),
            'average_score' => $this->statService->calculateAverageScoreByCompany($company),
            'total_endorsements' => $this->statService->countScorableEndorsementsByCompany($company),
            'average_promoter_index' => $this->statService->calculateAveragePromoterIndexByCompany($company)
        ];
    }
}