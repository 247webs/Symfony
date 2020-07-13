<?php

namespace AppBundle\BroadcastManagers;

use AppBundle\Document\EndorsementResponse;
use AppBundle\Document\Sharing\SharingProfile;
use AppBundle\Entity\User;
use AppBundle\Model\Share;

interface BroadcastManagerInterface
{
    public function broadcast() :array;

    public function assembleShares() :array;

    public function getBroadcaster(SharingProfile $sharingProfile);

    public function share(Share $share);

    public function getPostContent(Share $share, EndorsementResponse $endorsement);

    public function getBanner(User $user);
}
