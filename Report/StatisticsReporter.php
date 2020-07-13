<?php

namespace AppBundle\Report;

use AppBundle\Document\Statistic\BranchProfilePageView;
use AppBundle\Document\Statistic\BranchProfileSurveyLinkClick;
use AppBundle\Document\Statistic\CompanyProfilePageView;
use AppBundle\Document\Statistic\CompanyProfileSurveyLinkClick;
use AppBundle\Document\Statistic\OfferRequestDelivered;
use AppBundle\Document\Statistic\OfferRequestFailed;
use AppBundle\Document\Statistic\OfferRequestLinkClicked;
use AppBundle\Document\Statistic\OfferRequestOpened;
use AppBundle\Document\Statistic\OfferRequestSent;
use AppBundle\Document\Statistic\OfferResponseReceived;
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
            'offer_requests_sent' => $this->statService->countUserStats(
                $user,
                OfferRequestSent::class
            ),
            'offer_requests_delivered' => $this->statService->countUserStats(
                $user,
                OfferRequestDelivered::class
            ),
            'offer_requests_failed' => $this->statService->countUserStats(
                $user,
                OfferRequestFailed::class
            ),
            'offer_requests_opened' => $this->statService->countUserStats(
                $user,
                OfferRequestOpened::class
            ),
            'offer_request_links_clicked' => $this->statService->countUserStats(
                $user,
                OfferRequestLinkClicked::class
            ),
            'offer_responses_received' => $this->statService->countUserStats(
                $user,
                OfferResponseReceived::class
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
            'total_offers' => $this->statService->countScorableOffersByUser($user),
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
            'offer_requests_sent' => $this->statService->countBranchStats(
                $branch,
                OfferRequestSent::class
            ),
            'offer_requests_delivered' => $this->statService->countBranchStats(
                $branch,
                OfferRequestDelivered::class
            ),
            'offer_requests_failed' => $this->statService->countBranchStats(
                $branch,
                OfferRequestFailed::class
            ),
            'offer_requests_opened' => $this->statService->countBranchStats(
                $branch,
                OfferRequestOpened::class
            ),
            'offer_request_links_clicked' => $this->statService->countBranchStats(
                $branch,
                OfferRequestLinkClicked::class
            ),
            'offer_responses_received' => $this->statService->countBranchStats(
                $branch,
                OfferResponseReceived::class
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
            'total_offers' => $this->statService->countScorableOffersByBranch($branch),
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
            'offer_requests_sent' => $this->statService->countCompanyStats(
                $company,
                OfferRequestSent::class
            ),
            'offer_requests_delivered' => $this->statService->countCompanyStats(
                $company,
                OfferRequestDelivered::class
            ),
            'offer_requests_failed' => $this->statService->countCompanyStats(
                $company,
                OfferRequestFailed::class
            ),
            'offer_requests_opened' => $this->statService->countCompanyStats(
                $company,
                OfferRequestOpened::class
            ),
            'offer_request_links_clicked' => $this->statService->countCompanyStats(
                $company,
                OfferRequestLinkClicked::class
            ),
            'offer_responses_received' => $this->statService->countCompanyStats(
                $company,
                OfferResponseReceived::class
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
            'total_offers' => $this->statService->countScorableOffersByCompany($company),
            'average_promoter_index' => $this->statService->calculateAveragePromoterIndexByCompany($company)
        ];
    }
}