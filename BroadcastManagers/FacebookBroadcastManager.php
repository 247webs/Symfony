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

        // Find endorsements for each share.
        if (count($shares)) {
            $shares = $this->addEndorsementsToShares($shares, Broadcaster::FACEBOOK);
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
     * @param EndorsementResponse $endorsement
     * @return array
     */
    public function getPostContent(Share $share, EndorsementResponse $endorsement)
    {
        $content = [];
        $url = $this->container->getParameter('ng');
        $company = $share->getCompany();
        $branch  = $share->getBranch();
        $user = $share->getUser();

        /**
         * Deprecated message format due to Facebook API update 7.18.2017
         *
         * $message = $endorsement->getFirstName() . " from " . $endorsement->getCity() . ', ' .
         * $endorsement->getState() . ' endorsed %s';
         */

        /** Commented out in favor of link content 9.9.2017  */
//        $content['message'] =  number_format(($endorsement->getRating() * 100) / 20, 1)  .
//            ' Star Review on eEndorsements';
//
//        if (null !== $comments = $this->getEndorsementComments($endorsement)) {
//            $content['message'] .= ' -- ' . $comments;
//        }

        if (null !== $company) {
            $content = $this->formatCompanyPostContent($content, $company, $url, $endorsement);
        }

        if (null !== $branch) {
            $content = $this->formatBranchPostContent($content, $branch, $url, $endorsement);
        }

        if (null !== $user) {
            $content = $this->formatUserPostContent($content, $user, $url, $endorsement);
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
        EndorsementResponse $endorsementResponse,
        ManualShare $manualShare = null
    ) :array {

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/company/' . $company->getSlug() . '?endorsement=' . $endorsementResponse->getId();

            $description = $this->getVideoDescription($endorsementResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] = $url . '/company/' . $company->getSlug() .
                '?endorsement=' . $endorsementResponse->getId();
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
        EndorsementResponse $endorsementResponse,
        ManualShare $manualShare = null
    ) :array {

        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/branch/' . $branch->getSlug() . '?endorsement=' . $endorsementResponse->getId();

            $description = $this->getVideoDescription($endorsementResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] = $url . '/branch/' . $branch->getSlug() . '?endorsement=' . $endorsementResponse->getId();
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
        EndorsementResponse $endorsementResponse,
        ManualShare $manualShare = null
    ) :array {
        if ($manualShare && "video" === $manualShare->getSharingType()) {
            $link = $url . '/user/' . $user->getSlug() . '?endorsement=' . $endorsementResponse->getId();

            $description = $this->getVideoDescription($endorsementResponse);

            // Concatenate profile page url with the video description
            $description = $description . " " . $link;

            $content = [
                "description" => $description,
                "source" => $manualShare->getVideoUrl(),
                "file_url" => $manualShare->getVideoUrl()
            ];
        } else {
            $content['link'] =  $url . '/user/' . $user->getSlug() . '?endorsement=' . $endorsementResponse->getId();
        }

        return $content;
    }

    /**
     * @param EndorsementResponse $endorsementResponse
     * @return string
     */
    private function getVideoDescription(EndorsementResponse $endorsementResponse) :string
    {
        $description = $endorsementResponse->getFirstName() . " " . substr($endorsementResponse->getLastName(), 0, 1) .
            " in " . $endorsementResponse->getCity() . " " . $endorsementResponse->getState() .
            " Says, " . $this->getEndorsementComments($endorsementResponse);

        return $description;
    }

    /**
     * @param Share $share
     */
    private function shareAsPage(Share $share)
    {
        foreach ($share->getEndorsements() as $endorsement) {
            $content = $this->getPostContent($share, $endorsement);

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
                        $endorsement,
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
        foreach ($share->getEndorsements() as $endorsement) {
            $content = $this->getPostContent($share, $endorsement);

            if (null !== $content['link'] && null !== $share->getBroadcaster()->getToken()) {
                $response = $this->container->get('facebook_service')->postAsUser(
                    $share->getBroadcaster()->getToken(),
                    $this->getPostContent($share, $endorsement)
                );

                if (false !== $response) {
                    $this->recordShare(
                        $endorsement,
                        $response['id'],
                        Broadcaster::FACEBOOK,
                        $this->getShareType($share)
                    );
                }
            }
        }
    }
}
