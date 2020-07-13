<?php

namespace AppBundle\Entity;

use AppBundle\Utilities\ConstructorArgs;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserProfile
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserProfileRepository")
 * @ORM\Table(name="user_profile")
 * @Hateoas\Relation("self", href="expr('/user-profile/' ~ object.getId())")
 */
class UserProfile
{
    use ConstructorArgs;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public"})
     * @Assert\Blank(
     *     groups={"user_profile_post", "user_profile_put"}
     * )
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(type="string",length=100, nullable=false)
     * @Serializer\Groups({"public"})
     * @Assert\NotBlank(
     *     groups={"user_profile_post", "user_profile_put"}
     * )
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="100"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $logo;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true, options={"default":null})
     * @Serializer\Groups({"public"})
     */
    private $companyLogo;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileAddress",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $addresses;

    /**
     * @ORM\Column(type="text", nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="255"
     * )
     */
    private $video;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Url(
     *     groups={"user_profile_post", "user_profile_put"},
     * )
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="100"
     * )
     */
    private $website;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Email(
     *     groups={"user_profile_post", "user_profile_put"},
     * )
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="100"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="20"
     * )
     */
    private $phone1;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="20"
     * )
     */
    private $phone2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="255"
     * )
     */
    private $facebook_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="255"
     * )
     */
    private $twitter_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="255"
     * )
     */
    private $linkedin_url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"user_profile_post", "user_profile_put"},
     *     max="255"
     * )
     */
    private $profession;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileAccredidation",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $accredidations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileLicense",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $licenses;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfilePosition",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $positions;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileCustomButton",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $custom_buttons;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileTrackingIntegration",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $tracking_integrations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\ProfileService",
     *     mappedBy="user_profile",
     *     cascade={"persist", "remove"}
     * )
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $services;

    /**
     * @ORM\Column(type="text", nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $disclosure;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $average_rating;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $scorable_endorsements;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     */
    private $temp_rating_proceeded;

    /**
     * Constructor
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->addresses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->accredidations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->licenses = new \Doctrine\Common\Collections\ArrayCollection();
        $this->positions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->custom_buttons = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tracking_integrations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->services = new \Doctrine\Common\Collections\ArrayCollection();

        $this->handleArgs($args);
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return UserProfile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set logo
     *
     * @param string $logo
     *
     * @return UserProfile
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return UserProfile
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set video
     *
     * @param string $video
     *
     * @return UserProfile
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * Get video
     *
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * Set website
     *
     * @param string $website
     *
     * @return UserProfile
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserProfile
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone1
     *
     * @param string $phone1
     *
     * @return UserProfile
     */
    public function setPhone1($phone1)
    {
        $this->phone1 = $phone1;

        return $this;
    }

    /**
     * Get phone1
     *
     * @return string
     */
    public function getPhone1()
    {
        return $this->phone1;
    }

    /**
     * Set phone2
     *
     * @param string $phone2
     *
     * @return UserProfile
     */
    public function setPhone2($phone2)
    {
        $this->phone2 = $phone2;

        return $this;
    }

    /**
     * Get phone2
     *
     * @return string
     */
    public function getPhone2()
    {
        return $this->phone2;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return UserProfile
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add address
     *
     * @param \AppBundle\Entity\ProfileAddress $address
     *
     * @return UserProfile
     */
    public function addAddress(\AppBundle\Entity\ProfileAddress $address)
    {
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove address
     *
     * @param \AppBundle\Entity\ProfileAddress $address
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAddress(\AppBundle\Entity\ProfileAddress $address)
    {
        return $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Add accredidation
     *
     * @param \AppBundle\Entity\ProfileAccredidation $accredidation
     *
     * @return UserProfile
     */
    public function addAccredidation(\AppBundle\Entity\ProfileAccredidation $accredidation)
    {
        $this->accredidations[] = $accredidation;

        return $this;
    }

    /**
     * Remove accredidation
     *
     * @param \AppBundle\Entity\ProfileAccredidation $accredidation
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAccredidation(\AppBundle\Entity\ProfileAccredidation $accredidation)
    {
        return $this->accredidations->removeElement($accredidation);
    }

    /**
     * Get accredidations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccredidations()
    {
        return $this->accredidations;
    }

    /**
     * Add license
     *
     * @param \AppBundle\Entity\ProfileLicense $license
     *
     * @return UserProfile
     */
    public function addLicense(\AppBundle\Entity\ProfileLicense $license)
    {
        $this->licenses[] = $license;

        return $this;
    }

    /**
     * Remove license
     *
     * @param \AppBundle\Entity\ProfileLicense $license
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeLicense(\AppBundle\Entity\ProfileLicense $license)
    {
        return $this->licenses->removeElement($license);
    }

    /**
     * Get licenses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * Add position
     *
     * @param \AppBundle\Entity\ProfilePosition $position
     *
     * @return UserProfile
     */
    public function addPosition(\AppBundle\Entity\ProfilePosition $position)
    {
        $this->positions[] = $position;

        return $this;
    }

    /**
     * Remove position
     *
     * @param \AppBundle\Entity\ProfilePosition $position
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePosition(\AppBundle\Entity\ProfilePosition $position)
    {
        return $this->positions->removeElement($position);
    }

    /**
     * Get positions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Add customButton
     *
     * @param \AppBundle\Entity\ProfileCustomButton $customButton
     *
     * @return UserProfile
     */
    public function addCustomButton(\AppBundle\Entity\ProfileCustomButton $customButton)
    {
        $this->custom_buttons[] = $customButton;

        return $this;
    }

    /**
     * Remove customButton
     *
     * @param \AppBundle\Entity\ProfileCustomButton $customButton
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCustomButton(\AppBundle\Entity\ProfileCustomButton $customButton)
    {
        return $this->custom_buttons->removeElement($customButton);
    }

    /**
     * Get customButtons
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomButtons()
    {
        return $this->custom_buttons;
    }

    /**
     * Set pixel.
     *
     * @param string|null $pixel
     *
     * @return UserProfile
     */
    public function setPixel($pixel = null)
    {
        $this->pixel = $pixel;

        return $this;
    }

    /**
     * Add trackingIntegration.
     *
     * @param \AppBundle\Entity\ProfileTrackingIntegration $trackingIntegration
     *
     * @return UserProfile
     */
    public function addTrackingIntegration(\AppBundle\Entity\ProfileTrackingIntegration $trackingIntegration)
    {
        $this->tracking_integrations[] = $trackingIntegration;

        return $this;
    }

    /**
     * Remove trackingIntegration.
     *
     * @param \AppBundle\Entity\ProfileTrackingIntegration $trackingIntegration
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeTrackingIntegration(\AppBundle\Entity\ProfileTrackingIntegration $trackingIntegration)
    {
        return $this->tracking_integrations->removeElement($trackingIntegration);
    }

    /**
     * Get trackingIntegrations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrackingIntegrations()
    {
        return $this->tracking_integrations;
    }

    /**
     * Set companyLogo.
     *
     * @param string|null $companyLogo
     *
     * @return UserProfile
     */
    public function setCompanyLogo($companyLogo = null)
    {
        $this->companyLogo = $companyLogo;

        return $this;
    }

    /**
     * Get companyLogo.
     *
     * @return string|null
     */
    public function getCompanyLogo()
    {
        return $this->companyLogo;
    }

    /**
     * Set facebookUrl.
     *
     * @param string|null $facebookUrl
     *
     * @return UserProfile
     */
    public function setFacebookUrl($facebookUrl = null)
    {
        $this->facebook_url = $facebookUrl;

        return $this;
    }

    /**
     * Get facebookUrl.
     *
     * @return string|null
     */
    public function getFacebookUrl()
    {
        return $this->facebook_url;
    }

    /**
     * Set twitterUrl.
     *
     * @param string|null $twitterUrl
     *
     * @return UserProfile
     */
    public function setTwitterUrl($twitterUrl = null)
    {
        $this->twitter_url = $twitterUrl;

        return $this;
    }

    /**
     * Get twitterUrl.
     *
     * @return string|null
     */
    public function getTwitterUrl()
    {
        return $this->twitter_url;
    }

    /**
     * Set linkedinUrl.
     *
     * @param string|null $linkedinUrl
     *
     * @return UserProfile
     */
    public function setLinkedinUrl($linkedinUrl = null)
    {
        $this->linkedin_url = $linkedinUrl;

        return $this;
    }

    /**
     * Get linkedinUrl.
     *
     * @return string|null
     */
    public function getLinkedinUrl()
    {
        return $this->linkedin_url;
    }

    /**
     * Set profession.
     *
     * @param string|null $profession
     *
     * @return UserProfile
     */
    public function setProfession($profession = null)
    {
        $this->profession = $profession;

        return $this;
    }

    /**
     * Get profession.
     *
     * @return string|null
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * Add service.
     *
     * @param \AppBundle\Entity\ProfileService $service
     *
     * @return UserProfile
     */
    public function addService(\AppBundle\Entity\ProfileService $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service.
     *
     * @param \AppBundle\Entity\ProfileService $service
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeService(\AppBundle\Entity\ProfileService $service)
    {
        return $this->services->removeElement($service);
    }

    /**
     * Get services.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }
    /**
    * Set disclosure
    *
    * @param string $disclosure
    *
    * @return UserProfile
    */
    public function setDisclosure($disclosure)
    {
        $this->disclosure = $disclosure;

        return $this;
    }

   /**
    * Get disclosure
    *
    * @return string
    */
    public function getDisclosure()
    {
        return $this->disclosure;
    }

    /**
     * Set average_rating
     *
     * @param float $average_rating
     *
     * @return CompanyProfile
     */
    public function setAverageRating($average_rating)
    {
        $this->average_rating = $average_rating;

        return $this;
    }

    /**
     * Get average_rating
     *
     * @return string
     */
    public function getAverageRating()
    {
        return $this->average_rating;
    }

    /**
     * Set scorable_endorsements
     *
     * @param float $scorable_endorsements
     *
     * @return CompanyProfile
     */
    public function setScorableEndorsements($scorable_endorsements)
    {
        $this->scorable_endorsements = $scorable_endorsements;

        return $this;
    }

    /**
     * Get scorable_endorsements
     *
     * @return string
     */
    public function getScorableEndorsements()
    {
        return $this->scorable_endorsements;
    }

    /**
     * Set temp_rating_proceeded
     *
     * @param float $temp_rating_proceeded
     *
     * @return CompanyProfile
     */
    public function setTempRatingProceeded($temp_rating_proceeded)
    {
        $this->temp_rating_proceeded = $temp_rating_proceeded;

        return $this;
    }

    /**
     * Get temp_rating_proceeded
     *
     * @return string
     */
    public function getTempRatingProceeded()
    {
        return $this->temp_rating_proceeded;
    }
}
