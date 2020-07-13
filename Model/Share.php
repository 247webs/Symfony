<?php

namespace AppBundle\Model;

use AppBundle\Document\Sharing\Broadcaster\BroadcasterAbstract;
use AppBundle\Document\Sharing\SharingProfile;
use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;

class Share
{
    /**
     * @var array
     */
    private $endorsements;

    /**
     * @var BroadcasterAbstract
     */
    private $broadcaster;

    /**
     * @var SharingProfile
     */
    private $sharingProfile;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Branch;
     */
    private $branch;

    /**
     * @var Company
     */
    private $company;

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Branch|null
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param Branch $branch
     */
    public function setBranch(Branch $branch)
    {
        $this->branch = $branch;
    }

    /**
     * @return Company|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
    }

    /**
     * @return array
     */
    public function getEndorsements(): array
    {
        return $this->endorsements;
    }

    /**
     * @param array $endorsement
     */
    public function setEndorsements(array $endorsements)
    {
        $this->endorsements = $endorsements;
    }


    /**
     * @return BroadcasterAbstract
     */
    public function getBroadcaster(): BroadcasterAbstract
    {
        return $this->broadcaster;
    }

    /**
     * @param BroadcasterAbstract $broadcaster
     */
    public function setBroadcaster(BroadcasterAbstract $broadcaster)
    {
        $this->broadcaster = $broadcaster;
    }

    /**
     * @return SharingProfile
     */
    public function getSharingProfile(): SharingProfile
    {
        return $this->sharingProfile;
    }

    /**
     * @param SharingProfile $sharingProfile
     */
    public function setSharingProfile(SharingProfile $sharingProfile)
    {
        $this->sharingProfile = $sharingProfile;
    }
}

