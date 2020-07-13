<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FloifyApiKey
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FloifyApiKeyRepository")
 * @ORM\Table(name="floify_api_key")
 */
class FloifyApiKey
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100)
     */
    private $apiKey;

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
     * Set user.
     *
     * @param \AppBundle\Entity\User|null $user
     *
     * @return FloifyApiKey
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \AppBundle\Entity\User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set apiKey.
     *
     * @param string $apiKey
     *
     * @return FloifyApiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set loanStatus.
     *
     * @param string $loanStatus
     *
     * @return FloifyApiKey
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
