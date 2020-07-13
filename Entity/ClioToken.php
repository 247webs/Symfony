<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ClioToken
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ClioTokenRepository")
 * @ORM\Table(name="clio_token")
 */
class ClioToken
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"private"})
     * @Assert\Blank(
     *     groups={"clio_token_post", "clio_token_put"}
     * )
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
     * @ORM\Column(type="string", length=200, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Access Token is required"
     * )
     */
    private $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Token type is required"
     * )
     */
    private $tokenType;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Refresh Token is required"
     * )
     */
    private $refreshToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Redirect Uri is required"
     * )
     */
    private $redirectUri;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", length=11, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Token expiry time is required"
     * )
     */
    private $expiresIn;

    /**
     * @var datetime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"clio_token_post", "clio_token_put"},
     *     message="Token created at is required"
     * )
     */
    private $createdAt;

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
     * @return DrchronoPractice
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
     * Set accessToken.
     *
     * @param string $accessToken
     *
     * @return DrchronoPractice
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
     * @return DrchronoPractice
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
     * Set refreshToken.
     *
     * @param string $refreshToken
     *
     * @return DrchronoPractice
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get refreshToken.
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set redirectUri.
     *
     * @param string $redirectUri
     *
     * @return DrchronoPractice
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    /**
     * Get redirectUri.
     *
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * Set expiresIn.
     *
     * @param string $expiresIn
     *
     * @return DrchronoPractice
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Get expiresIn.
     *
     * @return string
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Set createdAt.
     *
     * @param string $createdAt
     *
     * @return DrchronoPractice
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}