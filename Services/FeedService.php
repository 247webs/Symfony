<?php

namespace AppBundle\Services;

use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\FeedSetting;
use AppBundle\Entity\User;
use AppBundle\Enumeration\FeedWidgetStyle;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FeedService
 * @package AppBundle\Services
 */
class FeedService
{
    /** @var EntityManager $em */
    private $em;

    /**
     * FeedService constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $type
     * @param string $slug
     * @return FeedSetting|mixed|null
     */
    public function getFeedSettings(string $type, string $slug)
    {
        // Determine if branch feed settings are overridden
        if ($type == 'branch') {
            $override = $this->getBranchOverride($slug);

            if ($override) {
                return $override;
            }
        }

        // Determine if user feed settings are overridden
        if ($type == 'user') {
            $override = $this->getUserOverride($slug);

            if ($override) {
                return $override;
            }
        }

        // Otherwise, return the feed settings
        $repo = $this->em->getRepository(FeedSetting::class);
        $feed = $repo->getFeedSettingByTypeAndSlug($type, $slug);

        if (!$feed) {
            throw new NotFoundHttpException("Feed not found");
        }

        return $feed;
    }

    /**
     * @param FeedSetting $feed
     * @return FeedSetting|mixed
     */
    public function setFeed(FeedSetting $feed)
    {
        $repo = $this->em->getRepository(FeedSetting::class);
        $f = $repo->getFeedExists($feed);
        $f = (!$f) ? new FeedSetting() : $f;

        if (null !== $feed->getCompany()) {
            $f->setCompany($this->em->getReference(Company::class, $feed->getCompany()->getId()));
        }
        if (null !== $feed->getBranch()) {
            $f->setBranch($this->em->getReference(Branch::class, $feed->getBranch()->getId()));
        }
        if (null !== $feed->getUser()) {
            $f->setUser($this->em->getReference(User::class, $feed->getUser()->getId()));
        }
        $f->setStyle(
            (strcasecmp($feed->getStyle(), FeedWidgetStyle::SLIDER) == 0) ?
                FeedWidgetStyle::SLIDER : FeedWidgetStyle::MARQUEE
        );
        $f->setShowReplies(true === $feed->getShowReplies() ? true : false);
        $f->setBackgroundColor($feed->getBackgroundColor());
        $f->setBorderColor($feed->getBorderColor());
        $f->setBorderWidth($feed->getBorderWidth());
        $f->setHeaderBackgroundColor($feed->getHeaderBackgroundColor());
        $f->setHeaderTextColor($feed->getHeaderTextColor());
        $f->setHeaderStarColor($feed->getHeaderStarColor());
        $f->setEndorsementSourceColor($feed->getEndorsementSourceColor());
        $f->setEndorsementTextColor($feed->getEndorsementTextColor());
        $f->setEndorsementReplyColor($feed->getEndorsementReplyColor());
        $f->setEndorsementStarColor($feed->getEndorsementStarColor());
        $f->setFooterBackgroundColor($feed->getFooterBackgroundColor());
        $f->setMinimumReviewValue($feed->getMinimumReviewValue());

        $this->em->persist($f);
        $this->em->flush();

        return $f;
    }

    /**
     * @param Company $company
     * @return bool
     */
    public function isFeedOverridden(Company $company)
    {
        return null !== $company->getSettings() && $company->getSettings()->getOverrideFeedWidget();
    }

    /**
     * @param string $slug
     * @return FeedSetting|null
     */
    private function getBranchOverride(string $slug)
    {
        $repo = $this->em->getRepository('AppBundle:FeedSetting');
        $branch = $this->em->getRepository('AppBundle:Branch')->findOneByIdOrSlug($slug);

        if (!$branch) {
            throw new NotFoundHttpException("Feed not found");
        }

        if ($this->isFeedOverridden($branch->getCompany())) {
            $type = 'company';
            $slug = $branch->getCompany()->getSlug();

            /** @var FeedSetting $feed */
            $feed = $repo->getFeedSettingByTypeAndSlug($type, $slug);

            if (!$feed) {
                throw new NotFoundHttpException("Feed not found");
            }

            $feed->setBranch($branch);
            $feed->setCompany(null);

            return $feed;
        }

        return null;
    }

    /**
     * @param string $slug
     * @return FeedSetting|null
     */
    private function getUserOverride(string $slug)
    {
        $repo = $this->em->getRepository('AppBundle:FeedSetting');
        $user = $this->em->getRepository('AppBundle:User')->findOneByIdOrSlug($slug);

        if (!$user) {
            throw new NotFoundHttpException("Feed not found");
        }

        if ($this->isFeedOverridden($user->getBranch()->getCompany())) {
            $type = 'company';
            $slug = $user->getBranch()->getCompany()->getSlug();

            /** @var FeedSetting $feed */
            $feed = $repo->getFeedSettingByTypeAndSlug($type, $slug);

            if (!$feed) {
                throw new NotFoundHttpException("Feed not found");
            }

            $feed->setUser($user);
            $feed->setCompany(null);

            return $feed;
        }

        return null;
    }
}
