<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints as CustomConstraint;
use AppBundle\Utilities\ConstructorArgs;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * *     href="expr('/contact/' ~ object.getId())"
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContactRepository")
 * @ORM\Table("contact")
 * @Serializer\ExclusionPolicy("all")
 * @CustomConstraint\ContactOwnerConstraint(
 *     groups={"contact_post", "contact_put"}
 * )
 */
class Contact
{
    use ConstructorArgs;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @var int
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @var User
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(name="first_name", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="First name can not be longer than 100 characters."
     * )
     */
    private $firstName;

    /**
     * @ORM\Column(name="last_name", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="Last name can not be longer than 100 characters."
     * )
     */
    private $lastName;

    /**
     * @ORM\Column(name="email", length=200, nullable=false, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\NotBlank(
     *     groups={"contact_post", "contact_put"},
     *     message="E-mail address can not be blank"
     * )
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="200",
     *     maxMessage="E-mail addresses can not be longer than 200 characters."
     * )
     * @CustomConstraint\KickboxApprovedConstraint(
     *     groups={"contact_post", "contact_put"}
     * )
     */
    private $email;

     /**
     * @ORM\Column(name="label", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="Label can not be longer than 100 characters."
     * )
     */
    private $label;

    /**
     * @ORM\Column(name="city", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="City can not be longer than 100 characters."
     * )
     */
    private $city;


    /**
     * @ORM\Column(name="state", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="State can not be longer than 100 characters."
     * )
     */
    private $state;

    /**
     * @ORM\Column(name="phone", length=20, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="20",
     *     maxMessage="Phone can not be longer than 20 characters."
     * )
     */
    private $phone;

    /**
     * @ORM\Column(name="secondary_phone", length=20, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="20",
     *     maxMessage="Secondary phone can not be longer than 20 characters."
     * )
     */
    private $secondaryPhone;

    /**
     * @ORM\Column(name="do_not_text", type="boolean", nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var bool
     * @Assert\Type(
     *     groups={"contact_post", "contact_put"},
     *     type="boolean"
     * )
     */
    private $doNotText;

    /**
     * @ORM\Column(name="facebook_im_address", length=100, nullable=true, options={"default": null})
     * @Serializer\Expose
     * @var string
     * @Assert\Length(
     *     groups={"contact_post", "contact_put"},
     *     max="100",
     *     maxMessage="Facebook IM Address can not be longer than 100 characters."
     * )
     */
    private $facebookImAddress;

    /**
     * @ORM\Column(name="active", type="boolean", options={"default": true})
     * @Serializer\Expose
     * @var bool
     * @Assert\NotNull(
     *     groups={"contact_put"}
     * )
     * @Assert\Type(
     *     groups={"contact_post", "contact_put"},
     *     type="boolean"
     * )
     */
    private $active;
    

    public function __construct(array $args = [])
    {
        $this->handleArgs($args);
    }

    private function getRoute()
    {
        return '/contact/';
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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Contact
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contact
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
     * Set label
     *
     * @param string $label
     *
     * @return Contact
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Contact
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     *
     * @return Contact
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Contact
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set secondaryPhone
     *
     * @param string $secondaryPhone
     *
     * @return Contact
     */
    public function setSecondaryPhone($secondaryPhone)
    {
        $this->secondaryPhone = $secondaryPhone;

        return $this;
    }

    /**
     * Get secondaryPhone
     *
     * @return string
     */
    public function getSecondaryPhone()
    {
        return $this->secondaryPhone;
    }

    /**
     * Set facebookImAddress
     *
     * @param string $facebookImAddress
     *
     * @return Contact
     */
    public function setFacebookImAddress($facebookImAddress)
    {
        $this->facebookImAddress = $facebookImAddress;

        return $this;
    }

    /**
     * Get facebookImAddress
     *
     * @return string
     */
    public function getFacebookImAddress()
    {
        return $this->facebookImAddress;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return Contact
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Contact
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
     * @return bool
     */
    public function isActive()
    {
        return !!$this->active;
    }

    /**
     * Set doNotText.
     *
     * @param bool $doNotText
     *
     * @return Contact
     */
    public function setDoNotText($doNotText)
    {
        $this->doNotText = $doNotText;

        return $this;
    }

    /**
     * Get doNotText.
     *
     * @return bool
     */
    public function getDoNotText()
    {
        return $this->doNotText;
    }
}
