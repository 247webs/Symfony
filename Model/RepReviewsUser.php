<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as Serializer;

class RepReviewsUser
{
    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $username;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $firstName;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $lastName;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     */
    private $isPaidUser;

    /**
     * @var array
     * @Serializer\Type("array")
     */
    private $surveyLinks;

    /**
     * @var integer
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $profilePageUrl;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     */
    private $active;

    /**
     * @var array
     * @Serializer\Type("array")
     */
    private $offers;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $partner_id;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
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
     * @return bool
     */
    public function isPaidUser(): bool
    {
        return $this->isPaidUser;
    }

    /**
     * @param bool $isPaidUser
     */
    public function setIsPaidUser(bool $isPaidUser)
    {
        $this->isPaidUser = $isPaidUser;
    }

    /**
     * @return array
     */
    public function getSurveyLinks(): array
    {
        return $this->surveyLinks;
    }

    /**
     * @param array $surveyLinks
     */
    public function setSurveyLinks(array $surveyLinks)
    {
        $this->surveyLinks = $surveyLinks;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getProfilePageUrl(): string
    {
        return $this->profilePageUrl;
    }

    /**
     * @param string $profilePageUrl
     */
    public function setProfilePageUrl(string $profilePageUrl)
    {
        $this->profilePageUrl = $profilePageUrl;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @return array
     */
    public function getOffers(): array
    {
        return $this->offers;
    }

    /**
     * @param array $offers
     */
    public function setOffers(array $offers)
    {
        $this->offers = $offers;
    }

    /**
     * @return string
     */
    public function getPartnerId(): string
    {
        return $this->partner_id;
    }

    /**
     * @param string $partner_id
     */
    public function setPartnerId($partner_id)
    {
        $this->partner_id = $partner_id;
    }
}
