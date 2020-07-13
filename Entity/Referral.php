<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints as CustomConstraint;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Referral
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReferralRepository")
 * @ORM\Table(name="referral")
 * @Hateoas\Relation("self", href="expr('/referral/' ~ object.getId())")
 * @CustomConstraint\ReferralEmailOrPhoneConstraint(
 *     groups={"referral_post"}
 * )
 */
class Referral
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups("referral")
     * @Assert\Blank(
     *     groups={"referral_post"}
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups("referral")
     * @Assert\NotBlank(
     *     groups={"referral_post"}
     * )
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Serializer\Groups("referral")
     * @Assert\NotBlank(
     *     groups={"referral_post"}
     * )
     * @Assert\Length(
     *     groups={"referral_post"},
     *     max="50"
     * )
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, options={"default": null})
     * @Serializer\Groups("referral")
     * @Assert\Length(
     *     groups={"referral_post"},
     *     max="50"
     * )
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups("referral")
     * @Assert\Email(
     *     groups={"referral_post"}
     * )
     * @Assert\Length(
     *     groups={"referral_post"},
     *     max="100"
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups("referral")
     * @Assert\Length(
     *     groups={"referral_post"},
     *     max="20"
     * )
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     * @Serializer\Groups("referral")
     */
    private $active;

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
     * @return Referral
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Referral
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Referral
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
     * Set phone
     *
     * @param string $phone
     *
     * @return Referral
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
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Referral
     */
    public function setUser(User $user = null)
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
     * Set active
     *
     * @param bool $active
     *
     * @return Referral
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
}
