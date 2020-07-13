<?php

namespace AppBundle\Model;

use AppBundle\Entity\User;
use AppBundle\Utilities\ConstructorArgs;

class AgileContact
{
    use ConstructorArgs;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $userMobilePhone;

    /**
     * @var string
     */
    private $userOfficePhone;

    /**
     * @var string
     */
    private $companyMobilePhone;

    /**
     * @var string
     */
    private $companyOfficePhone;

    /**
     * @var string
     */
    private $industry;

    /**
     * @var string
     */
    private $companyName;

    /**
     * @var string
     */
    private $plan;

    /**
     * @var string
     */
    private $couponCode;

    /**
     * @var string
     */
    private $referralPartner;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $userSlug;

    /**
     * @var string
     */
    private $role;

    /**
     * @var \DateTime
     */
    private $joinDate;

    /**
     * @var string
     */
    private $active;

    /**
     * @var string
     */
    private $stripeId;

    /**
     * @var string
     */
    private $branch;

    public function __construct(array $args = [])
    {
        $this->handleArgs($args);
    }

    /**
     * @param User $user
     * @return AgileContact
     */
    public static function createFromUser(User $user)
    {
        $params = [
            'firstName'  => $user->getFirstName(),
            'lastName'   => $user->getLastName(),
            'email'      => $user->getUsername(),
            'userSlug'   => $user->getSlug(),
            'userId'     => $user->getId(),
            'role'       => implode(', ', $user->getRoles()),
            'joinDate'   => $user->getCreated(),
            'active'     => $user->getActive() ? 'Yes' : 'No'
        ];

        if ($user->getProfile()) {
            if (null !== $phoneNumber1 = $user->getProfile()->getPhone1()) {
                $params['userMobilePhone'] = $phoneNumber1;
            }
            if (null !== $phoneNumber2 = $user->getProfile()->getPhone2()) {
                $params['userOfficePhone'] = $phoneNumber2;
            }
        }

        if ($user->getBranch() && $user->getBranch()->getCompany()) {
            if (null !== $industry = $user->getBranch()->getCompany()->getIndustry()) {
                $params['industry'] = $industry->getName();
            }
            if (null !== $companyName = $user->getBranch()->getCompany()->getName()) {
                $params['companyName'] = $companyName;
            }
        }

        if ($user->getBranch() && $user->getBranch()->getCompany() &&
            $companyProfile = $user->getBranch()->getCompany()->getProfile()
        ) {
            if (null !== $phoneNumber3 = $companyProfile->getPhone1()) {
                $params['companyMobilePhone'] = $phoneNumber3;
            }
            if (null !== $phoneNumber4 = $companyProfile->getPhone2()) {
                $params['companyOfficePhone'] = $phoneNumber4;
            }
        }

        if (null !== $plan = $user->getPlan()) {
            $params['plan'] = $plan->getId() . ' - ' . $plan->getDescription();
        }

        if (null !== $couponCode = $user->getStripeCouponId()) {
            $params['couponCode'] = $couponCode;
        }

        if (null !== $referralPartner = $user->getReseller()) {
            $params['referralPartner'] = $referralPartner->getId() . ' - ' . $referralPartner->getName();
        }

        if (null !== $branch = $user->getBranch()) {
            $params['branch'] = $branch->getId() . ' - ' . $branch->getName();
        }

        if (null !== $stripeId = $user->getStripeId()) {
            $params['stripeId'] = $user->getStripeId();
        }

        return new self($params);
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUserMobilePhone()
    {
        return $this->userMobilePhone;
    }

    /**
     * @param string $userMobilePhone
     */
    public function setUserMobilePhone(string $userMobilePhone)
    {
        $this->userMobilePhone = $userMobilePhone;
    }

    /**
     * @return string
     */
    public function getUserOfficePhone()
    {
        return $this->userOfficePhone;
    }

    /**
     * @param string $userOfficePhone
     */
    public function setUserOfficePhone(string $userOfficePhone)
    {
        $this->userOfficePhone = $userOfficePhone;
    }

    /**
     * @return string
     */
    public function getCompanyMobilePhone()
    {
        return $this->companyMobilePhone;
    }

    /**
     * @param string $companyMobilePhone
     */
    public function setCompanyMobilePhone(string $companyMobilePhone)
    {
        $this->companyMobilePhone = $companyMobilePhone;
    }

    /**
     * @return string
     */
    public function getCompanyOfficePhone()
    {
        return $this->companyOfficePhone;
    }

    /**
     * @param string $companyOfficePhone
     */
    public function setCompanyOfficePhone(string $companyOfficePhone)
    {
        $this->companyOfficePhone = $companyOfficePhone;
    }

    /**
     * @return string
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * @param string $industry
     */
    public function setIndustry(string $industry)
    {
        $this->industry = $industry;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName(string $companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param string $plan
     */
    public function setPlan(string $plan)
    {
        $this->plan = $plan;
    }

    /**
     * @return string
     */
    public function getCouponCode()
    {
        return $this->couponCode;
    }

    /**
     * @param string $couponCode
     */
    public function setCouponCode(string $couponCode)
    {
        $this->couponCode = $couponCode;
    }

    /**
     * @return string
     */
    public function getReferralPartner()
    {
        return $this->referralPartner;
    }

    /**
     * @param string $referralPartner
     */
    public function setReferralPartner(string $referralPartner)
    {
        $this->referralPartner = $referralPartner;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserSlug(): string
    {
        return $this->userSlug;
    }

    /**
     * @param string $userSlug
     */
    public function setUserSlug(string $userSlug)
    {
        $this->userSlug = $userSlug;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }

    /**
     * @return \DateTime
     */
    public function getJoinDate(): \DateTime
    {
        return $this->joinDate;
    }

    /**
     * @param \DateTime $joinDate
     */
    public function setJoinDate(\DateTime $joinDate)
    {
        $this->joinDate = $joinDate;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $active
     */
    public function setActive(string $active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getStripeId()
    {
        return $this->stripeId;
    }

    /**
     * @param string $stripeId
     */
    public function setStripeId(string $stripeId)
    {
        $this->stripeId = $stripeId;
    }

    /**
     * @return int
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param int $branch
     */
    public function setBranch(string $branch)
    {
        $this->branch = $branch;
    }
}
