<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ResellerRepository")
 * @ORM\Table(name="reseller")
 *
 * @package AppBundle\Entity
 */
class Reseller
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public", "private"})
     * @Assert\Blank(
     *     groups={"post", "put"}
     * )
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *     groups={"post", "put"}
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=32, unique=true, nullable=false)
     * @Serializer\Groups({"public", "private"})
     */
    protected $reseller_key;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"post", "put"}
     * )
     * @Assert\Type(
     *     groups={"post", "put"},
     *     type="boolean"
     * )
     */
    protected $bundled;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $feedLogo;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $profileLogo;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $dashboardLogo;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $twitterLogo;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $linkedInLogo;

    /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true, options={"default": null})
     * @Serializer\Groups({"public", "private"})
     */
    protected $facebookLogo;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"put_administrators"}
     * )
     */
    protected $resellerAdministrators;

    /**
     * @return array
     */
    public function getResellerAdministrators(): array
    {
        return $this->resellerAdministrators;
    }

    /**
     * @param array $resellerAdministrators
     */
    public function setResellerAdministrators(array $resellerAdministrators)
    {
        $this->resellerAdministrators = $resellerAdministrators;
    }

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
     * Set name.
     *
     * @param string $name
     *
     * @return Reseller
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set resellerKey.
     *
     * @param string $resellerKey
     *
     * @return Reseller
     */
    public function setResellerKey($resellerKey)
    {
        $this->reseller_key = $resellerKey;

        return $this;
    }

    /**
     * Get resellerKey.
     *
     * @return string
     */
    public function getResellerKey()
    {
        return $this->reseller_key;
    }

    /**
     * Set bundled.
     *
     * @param bool $bundled
     *
     * @return Reseller
     */
    public function setBundled($bundled)
    {
        $this->bundled = $bundled;

        return $this;
    }

    /**
     * Get bundled.
     *
     * @return bool
     */
    public function getBundled()
    {
        return $this->bundled;
    }

    /**
     * Set feedLogo.
     *
     * @param string|null $feedLogo
     *
     * @return Reseller
     */
    public function setFeedLogo($feedLogo = null)
    {
        $this->feedLogo = $feedLogo;

        return $this;
    }

    /**
     * Get feedLogo.
     *
     * @return string|null
     */
    public function getFeedLogo()
    {
        return $this->feedLogo;
    }

    /**
     * Set profileLogo.
     *
     * @param string|null $profileLogo
     *
     * @return Reseller
     */
    public function setProfileLogo($profileLogo = null)
    {
        $this->profileLogo = $profileLogo;

        return $this;
    }

    /**
     * Get profileLogo.
     *
     * @return string|null
     */
    public function getProfileLogo()
    {
        return $this->profileLogo;
    }

    /**
     * Set dashboardLogo.
     *
     * @param string|null $dashboardLogo
     *
     * @return Reseller
     */
    public function setDashboardLogo($dashboardLogo = null)
    {
        $this->dashboardLogo = $dashboardLogo;

        return $this;
    }

    /**
     * Get dashboardLogo.
     *
     * @return string|null
     */
    public function getDashboardLogo()
    {
        return $this->dashboardLogo;
    }

    /**
     * Set twitterLogo.
     *
     * @param string|null $twitterLogo
     *
     * @return Reseller
     */
    public function setTwitterLogo($twitterLogo = null)
    {
        $this->twitterLogo = $twitterLogo;

        return $this;
    }

    /**
     * Get twitterLogo.
     *
     * @return string|null
     */
    public function getTwitterLogo()
    {
        return $this->twitterLogo;
    }

    /**
     * Set linkedInLogo.
     *
     * @param string|null $linkedInLogo
     *
     * @return Reseller
     */
    public function setLinkedInLogo($linkedInLogo = null)
    {
        $this->linkedInLogo = $linkedInLogo;

        return $this;
    }

    /**
     * Get linkedInLogo.
     *
     * @return string|null
     */
    public function getLinkedInLogo()
    {
        return $this->linkedInLogo;
    }

    /**
     * Set facebookLogo.
     *
     * @param string|null $facebookLogo
     *
     * @return Reseller
     */
    public function setFacebookLogo($facebookLogo = null)
    {
        $this->facebookLogo = $facebookLogo;

        return $this;
    }

    /**
     * Get facebookLogo.
     *
     * @return string|null
     */
    public function getFacebookLogo()
    {
        return $this->facebookLogo;
    }
}
