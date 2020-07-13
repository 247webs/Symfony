<?php

namespace AppBundle\BroadcastManagers;

use AppBundle\Enumeration\Broadcaster;
use AppBundle\Model\ManualShare;
use AppBundle\Model\Share;

class TwitterBroadcastManager extends BroadcastManagerAbstract implements BroadcastManagerInterface
{
    public function broadcast(): array
    {
        $shares = $this->assembleShares();

        if (count($shares)) {
            /** @var Share $share */
            foreach ($shares as $share) {
                $this->share($share);
            }
        }

        return $shares;
    }

    public function assembleShares(): array
    {
        $shares = [];
        $sharers = $this->getSharers();

        // Start building shares
        if (count($sharers)) {
            /**
             * @var  $key
             * @var AutoSharing $sharer
             */
            foreach ($sharers as $key => $sharer) {
                /** @var SharingProfile $sharingProfile */
                $sharingProfile = $sharer->getSharingProfile();

                /** @var TwitterBroadcaster $broadcaster */
                $broadcaster = $this->getBroadcaster($sharingProfile);

                // Remove sharers without a Twitter Broadcaster or who are inactive
                if (false === $broadcaster || !$this->getEntityCanShare($sharer)) {
                    unset($sharers[$key]);
                } else { // otherwise, build the share
                    $share = $this->buildShare(null, $broadcaster, $sharingProfile);
                    $shares[] = $share;
                    unset($tmp);
                }
            }
        }

        // Find endorsements for each share.
        if (count($shares)) {
            $shares = $this->addEndorsementsToShares($shares, Broadcaster::TWITTER);
        }

        return $shares;
    }

    public function getBroadcaster(SharingProfile $sharingProfile)
    {
        if (null !== $sharingProfile && count($sharingProfile->getBroadcasters())) {
            /** @var BroadcasterAbstract $broadcaster */
            foreach ($sharingProfile->getBroadcasters() as $broadcaster) {
                if ($broadcaster instanceof TwitterBroadcaster) {
                    return $broadcaster;
                }
            }
        }

        return false;
    }

    public function share(Share $share)
    {
        foreach ($share->getEndorsements() as $endorsement) {
            $content = $this->getPostContent($share, $endorsement);

            if (null !== $content->status) {
                /** @var TwitterBroadcaster $broadcaster */
                $broadcaster = $share->getBroadcaster();

                $response = $this->container->get('twitter_service')->post(
                    $broadcaster->getToken(),
                    $broadcaster->getTokenSecret(),
                    $content->status,
                    $content->image
                );

                if (false !== $response) {
                    $this->recordShare(
                        $endorsement,
                        $response,
                        Broadcaster::TWITTER,
                        $this->getShareType($share)
                    );
                }
            }
        }
    }

    public function getPostContent(Share $share, EndorsementResponse $endorsement)
    {
        $company = $share->getCompany();
        $branch  = $share->getBranch();
        $user = $share->getUser();
        $url = $this->container->getParameter('ng');
        $content = new \stdClass;

        $content->status = $this->getEndorsementComments($endorsement);

        if (null !== $content->status) {
            if (null !== $company) {
                $content = $this->formatCompanyPostContent($content, $company, $url);
            }

            if (null !== $branch) {
                $content = $this->formatBranchPostContent($content, $branch, $url);
            }

            if (null !== $user) {
                $content = $this->formatUserPostContent($content, $user, $url);
            }

            $content->status = $this->trimStatus($content->status);
        }

        return $content;
    }

    /**
     * @param User $user
     * @return string
     */
    public function getBanner(User $user)
    {
        $customBanner = $this->getCustomBanner($user, 'twitter');

        if (false !== $customBanner) {
            return $customBanner;
        }

        return ($user->getReseller() && null !== $user->getReseller()->getTwitterLogo()) ?
            $this->awsResellerImagePrefix . $user->getReseller()->getTwitterLogo() :
            $this->banner;
    }

    public function formatCompanyPostContent(
        \stdClass $content,
        Company $company,
        string $url,
        ManualShare $manualShare = null
    ) {
        $content->status .= ' ' . $url . '/company/' . $company->getSlug();

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $content->image = null;
            $content->video = $manualShare->getVideoUrl();
        } else {
            $user = $this->userRepo->getCompanyAdministrator($company);
            $content->image = ($user) ? $this->getBanner($user) : $this->banner;
            $content->video = null;
        }

        return $content;
    }

    public function formatBranchPostContent(
        \stdClass $content,
        Branch $branch,
        string $url,
        ManualShare $manualShare = null
    ) {
        $content->status .= ' ' . $url . '/branch/' . $branch->getSlug();

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $content->image = null;
            $content->video = $manualShare->getVideoUrl();
        } else {
            $user = $this->userRepo->getBranchAdministrator($branch);
            $content->image = ($user) ? $this->getBanner($user) : $this->banner;
            $content->video = null;
        }

        return $content;
    }

    public function formatUserPostContent(
        \stdClass $content,
        User $user,
        string $url,
        ManualShare $manualShare = null
    ) {
        $content->status .= ' ' . $url . '/user/' . $user->getSlug();

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $content->image = null;
            $content->video = $manualShare->getVideoUrl();
        } else {
            $content->image = $this->getBanner($user);
            $content->video = null;
        }

        return $content;
    }

    /**
     * @param string $status
     * @return string
     */
    public function trimStatus(string $status)
    {
        // Twitter character limit enforcement
        if (strlen($status) > 140) {
            $reg = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i';

            preg_match_all($reg, $status, $matches, PREG_PATTERN_ORDER);
            $cut_string = substr($status, 0, (140-strlen($matches[0][0])-1));
            $status = $cut_string . " " . $matches[0][0];
        }

        return $status;
    }
}
