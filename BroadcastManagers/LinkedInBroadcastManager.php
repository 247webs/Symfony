<?php

namespace AppBundle\BroadcastManagers;

use AppBundle\Enumeration\Broadcaster;
use AppBundle\Model\Share;

class LinkedInBroadcastManager extends BroadcastManagerAbstract implements BroadcastManagerInterface
{
    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

                /** @var LinkedInBroadcaster $broadcaster */
                $broadcaster = $this->getBroadcaster($sharingProfile);

                // Remove sharers without a LinkedIn Broadcaster or who are inactive
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
            $shares = $this->addEndorsementsToShares($shares, Broadcaster::LINKEDIN);
        }

        return $shares;
    }

    /**
     * @param SharingProfile $sharingProfile
     * @return BroadcasterAbstract|bool
     */
    public function getBroadcaster(SharingProfile $sharingProfile)
    {
        if (null !== $sharingProfile && count($sharingProfile->getBroadcasters())) {
            /** @var BroadcasterAbstract $broadcaster */
            foreach ($sharingProfile->getBroadcasters() as $broadcaster) {
                if ($broadcaster instanceof LinkedInBroadcaster) {
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
        foreach ($share->getEndorsements() as $endorsement) {
            $content = $this->getPostContent($share, $endorsement);

            if (!empty($content)) {
                $response = $this->container->get('linkedin_service')->post(
                    $share->getBroadcaster()->getToken(),
                    $content
                );

                if (false !== $response) {
                    $this->recordShare(
                        $endorsement,
                        (property_exists($response, 'id')) ? $response->id : 'NO UPDATE KEY',
                        Broadcaster::LINKEDIN,
                        $this->getShareType($share)
                    );
                } else {
                    $this->recordShare(
                        $endorsement,
                        'SHARE FAILED',
                        Broadcaster::LINKEDIN,
                        $this->getShareType($share)
                    );
                }
            }
        }
    }

    /**
     * @param Share $share
     * @param EndorsementResponse $endorsement
     * @return array
     */
    public function getPostContent(Share $share, EndorsementResponse $endorsement)
    {
        /** @var LinkedInBroadcaster $broadcaster */
        $broadcaster = $share->getBroadcaster();

        if (!$broadcaster->getPersonURN()) {
            $this->container->get('broadcaster_service')->saveLinkedInPersonURN($broadcaster);
        }

        /** @var array $content */
        $content = [
            'author' => "urn:li:person:" . $broadcaster->getPersonURN(),
            'lifecycleState' => 'PUBLISHED',
            'specificContent' => [
                'com.linkedin.ugc.ShareContent' => [
                    'shareCommentary' => ['text' => $this->getEndorsementComments($endorsement)],
                    'shareMediaCategory' => 'ARTICLE',
                    'media' => [[
                        'status' => 'READY',
                        'originalUrl' => '',
                        'title' => ['text' => '']
                    ]]
                ]
            ],
            'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC']
        ];

        /** @var string $url */
        $url = $this->container->getParameter('ng');

        /** @var Company $company */
        $company = $share->getCompany();

        /** @var Branch $branch */
        $branch  = $share->getBranch();

        /** @var User $user */
        $user = $share->getUser();

        /** @var string $title */
        $title = $endorsement->getFirstName() . " from " . $endorsement->getCity() . ', '
            . $endorsement->getState() . ' endorsed %s';

        if (null !== $company) {
            $content = $this->formatCompanyPostContent($content, $company, $title, $url);
        }

        if (null !== $branch) {
            $content = $this->formatBranchPostContent($content, $branch, $title, $url);
        }

        if (null !== $user) {
            $content = $this->formatUserPostContent($content, $user, $title, $url);
        }

        return $content;
    }

    /**
     * @param User $user
     * @return string
     */
    public function getBanner(User $user)
    {
        $customBanner = $this->getCustomBanner($user, 'linkedin');

        if (false !== $customBanner) {
            return $customBanner;
        }

        return ($user->getReseller() && null !== $user->getReseller()->getLinkedInLogo()) ?
            $this->awsResellerImagePrefix . $user->getReseller()->getLinkedInLogo() :
            $this->banner;
    }

    /**
     * @param array $content
     * @param Company $company
     * @param string $title
     * @param string $url
     * @param string $videoFile
     * @return array
     */
    public function formatCompanyPostContent(
        array $content,
        Company $company,
        string $title,
        string $url,
        string $videoFile = null
    ) {
        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['title']['text'] =
            sprintf($title, "us");
        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['originalUrl'] =
            $url . '/company/' . $company->getSlug();

        // If we're posting an image, retrieve URL for social media banner
        if ('IMAGE' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $user = $this->userRepo->getCompanyAdministrator($company);
            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] =
                ($user) ? $this->getBanner($user) : $this->banner;
        }
        // If we're posting a video, retrieve video URL
        if ('VIDEO' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] = $videoFile;
        }

        return $content;
    }

    /**
     * @param array $content
     * @param Branch $branch
     * @param string $title
     * @param string $url
     * @param string $videoFile
     * @return array
     */
    public function formatBranchPostContent(
        array $content,
        Branch $branch,
        string $title,
        string $url,
        string $videoFile = null
    ) {
        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['title']['text'] =
            sprintf($title, "us");
        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['originalUrl']
            =  $url . '/branch/' . $branch->getSlug();

        // If we're posting an image, retrieve URL for social media banner
        if ('IMAGE' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $user = $this->userRepo->getBranchAdministrator($branch);

            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] =
                ($user) ? $this->getBanner($user) : $this->banner;
        }

        // If we're posting a video, retrieve video URL
        if ('VIDEO' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] = $videoFile;
        }

        return $content;
    }

    /**
     * @param array $content
     * @param User $user
     * @param string $title
     * @param string $url
     * @param string $videoFile
     * @return array
     */
    public function formatUserPostContent(
        array $content,
        User $user,
        string $title,
        string $url,
        string $videoFile = null
    ) {

        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['title']['text'] =
            sprintf($title, "me");
        $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['originalUrl'] =
            $url . '/user/' . $user->getSlug();

        // If we're posting an image, retrieve URL for social media banner
        if ('IMAGE' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] =
                ($user) ? $this->getBanner($user) : $this->banner;
        }

        // If we're posting a video, retrieve video URL
        if ('VIDEO' == $content['specificContent']['com.linkedin.ugc.ShareContent']['shareMediaCategory']) {
            $content['specificContent']['com.linkedin.ugc.ShareContent']['media'][0]['media'] = $videoFile;
        }

        return $content;
    }
}
