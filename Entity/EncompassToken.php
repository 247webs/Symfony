<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EncompassToken
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EncompassTokenRepository")
 * @ORM\Table(name="encompass_token")
 */
class EncompassToken
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $instanceId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200)
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000)
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     */
    private $tokenType;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     * 
     */
    private $tokenExpire;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=true)
     * 
     */
    private $lastUsed;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private $loanStatus;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set company.
     *
     * @param \AppBundle\Entity\Company|null $company
     *
     * @return EncompassToken
     */
    public function setCompany(\AppBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return \AppBundle\Entity\Company|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set instanceId.
     *
     * @param string $instanceId
     *
     * @return EncompassToken
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * Get instanceId.
     *
     * @return string
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * Set clientId.
     *
     * @param string $clientId
     *
     * @return EncompassToken
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set clientSecret.
     *
     * @param string $clientSecret
     *
     * @return EncompassToken
     */
    public function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    /**
     * Get clientSecret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Set accessToken.
     *
     * @param string $accessToken
     *
     * @return EncompassToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set tokenType.
     *
     * @param string $tokenType
     *
     * @return EncompassToken
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    /**
     * Get tokenType.
     *
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Set tokenExpire.
     *
     * @param string $tokenExpire
     *
     * @return EncompassToken
     */
    public function setTokenExpire($tokenExpire)
    {
        $this->tokenExpire = $tokenExpire;

        return $this;
    }

    /**
     * Get tokenExpire.
     *
     * @return string
     */
    public function getTokenExpire()
    {
        return $this->tokenExpire;
    }

    /**
     * Set lastUsed.
     *
     * @param string $lastUsed
     *
     * @return EncompassToken
     */
    public function setLastUsed($lastUsed)
    {
        $this->lastUsed = $lastUsed;

        return $this;
    }

    /**
     * Get lastUsed.
     *
     * @return string
     */
    public function getLastUsed()
    {
        return $this->lastUsed;
    }

    /**
     * Set loanStatus.
     *
     * @param string $loanStatus
     *
     * @return EncompassToken
     */
    public function setLoanStatus($loanStatus)
    {
        $this->loanStatus = $loanStatus;

        return $this;
    }

    /**
     * Get loanStatus.
     *
     * @return string
     */
    public function getLoanStatus()
    {
        return $this->loanStatus;
    }
}
