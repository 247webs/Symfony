<?php

namespace AppBundle\BroadcastManagers;

use AppBundle\Enumeration\Broadcaster;
use AppBundle\Model\Share;
use AppBundle\Model\ManualShare;

class FacebookBroadcastManager extends BroadcastManagerAbstract implements BroadcastManagerInterface
{
    /**
     * @return array
     */
    public function broadcast() :array
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

    /**
     * @return array
     */
    public function assembleShares() :array
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

                /** @var FacebookBroadcaster $broadcaster */
                $broadcaster = $this->getBroadcaster($sharingProfile);

                // Remove sharers without a Facebook Broadcaster or who are inactive
                if (false === $broadcaster || !$this->getEntityCanShare($sharer)) {
                    unset($sharers[$key]);
                } else { // otherwise, build the share
                    $share = $this->buildShare(null, $broadcaster, $sharingProfile);
                    $shares[] = $share;
                    unset($tmp);
                }
            }
        }

        // Find offers for each share.
        if (count($shares)) {
            $shares = $this->addOffersToShares($shares, Broadcaster::FACEBOOK);
        }

        return $shares;
    }

    /**
     * @param SharingProfile|null $sharingProfile
     * @return BroadcasterAbstract|bool
     */
    public function getBroadcaster(SharingProfile $sharingProfile = null)
    {
        if (null !== $sharingProfile && count($sharingProfile->getBroadcasters())) {
            /** @var BroadcasterAbstract $broadcaster */
            foreach ($sharingProfile->getBroadcasters() as $broadcaster) {
                if ($broadcaster instanceof FacebookBroadcaster) {
                    return $broadcaster;
                }
            }
        }

        return false;
    }

    /**
     * @param Share $share
     */
    public function share(Share $share)
    {
        /** @var FacebookBroadcaster $broadcaster */
        $broadcaster = $share->getBroadcaster();

        (null !== $broadcaster->getPageId()) ? $this->shareAsPage($share) : $this->shareAsPerson($share);
    }

    /**
     * @param Share $share
     * @param OfferResponse $offer
     * @return array
     */
    public function getPostContent(Share $share, OfferResponse $offer)
    {
        $content = [];
        $url = $this->container->getParameter('ng');
        $company = $share->getCompany();
        $branch  = $share->getBranch();
        $user = $share->getUser();

        /**
         * Deprecated message format due to Facebook API update 7.18.2017
         *
         * $message = $offer->getFirstName() . " from " . $offer->getCity() . ', ' .
         * $offer->getState() . ' endorsed %s';
         */

        /** Commented out in favor of link content 9.9.2017  */
//        $content['message'] =  number_format(($offer->getRating() * 100) / 20, 1)  .
//            ' Star Review on eOffers';
//
//        if (null !== $comments = $this->getOfferComments($offer)) {
//            $content['message'] .= ' -- ' . $comments;
//        }

        if (null !== $company) {
            $content = $this->formatCompanyPostContent($content, $company, $url, $offer);
        }

        if (null !== $branch) {
            $content = $this->formatBranchPostContent($content, $branch, $url, $offer);
        }

        if (null !== $user) {
            $content = $this->formatUserPostContent($content, $user, $url, $offer);
        }

        return $content;
    }

    /**
     * @param User $user
     * @return string
     *
     * Due to changes to Facebook's Open Graph API on 7.18.2017, we're no longer using this method.
     * It's left here to conform to BroadcastManager interface requirements.
     */
    public function getBanner(User $user)
    {
        $customBanner = $this->getCustomBanner($user, 'facebook');

        if (false !== $customBanner) {
            return $customBanner;
        }

        return ($user->getReseller() && null !== $user->getReseller()->getFacebookLogo()) ?
            $this->awsResellerImagePrefix . $user->getReseller()->getFacebookLogo() :
            $this->banner;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param string $url
     * @return array
     */
    public function formatCompanyPostContent(
        array $content,
        Company $company,
        string $url,
        OfferResponse $offerResponse,
        ManualShare $manualShare = null
    ) :array {

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/company/' . $company->getSlug() . '?offer=' . $offerResponse->getId();

            $description = $this->getVideoDescription($offerResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] = $url . '/company/' . $company->getSlug() .
                '?offer=' . $offerResponse->getId();
        }

        return $content;
    }

    /**
     * @param array $content
     * @param Branch $branch
     * @param string $url
     * @return array
     */
    public function formatBranchPostContent(
        array $content,
        Branch $branch,
        string $url,
        OfferResponse $offerResponse,
        ManualShare $manualShare = null
    ) :array {

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/branch/' . $branch->getSlug() . '?offer=' . $offerResponse->getId();

            $description = $this->getVideoDescription($offerResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] = $url . '/branch/' . $branch->getSlug() . '?offer=' . $offerResponse->getId();
        }

        return $content;
    }

    /**
     * @param array $content
     * @param User $user
     * @param string $url
     * @return array
     */
    public function formatUserPostContent(
        array $content,
        User $user,
        string $url,
        OfferResponse $offerResponse,
        ManualShare $manualShare = null
    ) :array {
        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/user/' . $user->getSlug() . '?offer=' . $offerResponse->getId();

            $description = $this->getVideoDescription($offerResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] =  $url . '/user/' . $user->getSlug() . '?offer=' . $offerResponse->getId();
        }

        return $content;
    }

    /**
     * @param OfferResponse $offerResponse
     * @return string
     */
    private function getVideoDescription(OfferResponse $offerResponse) :string
    {
        $description = $offerResponse->getFirstName() . " " . substr($offerResponse->getLastName(), 0, 1) .
            " in " . $offerResponse->getCity() . " " . $offerResponse->getState() .
            " Says, " . $this->getOfferComments($offerResponse);

        return $description;
    }

    /**
     * @param Share $share
     */
    private function shareAsPage(Share $share)
    {
        foreach ($share->getOffers() as $offer) {
            $content = $this->getPostContent($share, $offer);

            if (null !== $content['link']) {
                /** @var FacebookBroadcaster $broadcaster */
                $broadcaster = $share->getBroadcaster();

                $response = $this->container->get('facebook_service')->postAsPage(
                    $broadcaster->getToken(),
                    $broadcaster->getPageId(),
                    $content
                );

                if (false !== $response) {
                    $this->recordShare(
                        $offer,
                        $response['id'],
                        Broadcaster::FACEBOOK,
                        $this->getShareType($share)
                    );
                }
            }
        }
    }

    /**
     * @param Share $share
     */
    private function shareAsPerson(Share $share)
    {
        foreach ($share->getOffers() as $offer) {
            $content = $this->getPostContent($share, $offer);

            if (null !== $content['link'] && null !== $share->getBroadcaster()->getToken()) {
                $response = $this->container->get('facebook_service')->postAsUser(
                    $share->getBroadcaster()->getToken(),
                    $this->getPostContent($share, $offer)
                );

                if (false !== $response) {
                    $this->recordShare(
                        $offer,
                        $response['id'],
                        Broadcaster::FACEBOOK,
                        $this->getShareType($share)
                    );
                }
            }
        }
    }
}
