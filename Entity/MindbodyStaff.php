<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class MindbodyStaff
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MindbodyStaffRepository")
 * @ORM\Table(name="mindbody_staff")
 */
class MindbodyStaff
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"private"})
     * @Assert\Blank(
     *     groups={"mindbody_staff_post", "mindbody_staff_put"}
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MindbodyToken")
     * @ORM\JoinColumn(name="mindbody_token_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"mindbody_staff_post", "mindbody_staff_put"}
     * )
     */
    private $mindbodyToken;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"mindbody_staff_post", "mindbody_staff_put"}
     * )
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"mindbody_staff_post", "mindbody_staff_put"},
     *     message="Mindbody Staff ID is required"
     * )
     */
    private $mindbodyStaffId;

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
     * Set mindbodyToken.
     *
     * @param \AppBundle\Entity\MindbodyToken|null $mindbodyToken
     *
     * @return MindbodyStaff
     */
    public function setMindbodyToken(\AppBundle\Entity\MindbodyToken $mindbodyToken)
    {
        $this->mindbodyToken = $mindbodyToken;

        return $this;
    }

    /**
     * Get mindbodyToken.
     *
     * @return \AppBundle\Entity\MindbodyToken|null
     */
    public function getMindbodyToken()
    {
        return $this->mindbodyToken;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User|null $user
     *
     * @return MindbodyStaff
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
     * Set mindbodyStaffId.
     *
     * @param string $mindbodyStaffId
     *
     * @return MindbodyStaff
     */
    public function setMindbodyStaffId($mindbodyStaffId)
    {
        $this->mindbodyStaffId = $mindbodyStaffId;

        return $this;
    }

    /**
     * Get mindbodyStaffId.
     *
     * @return string
     */
    public function getMindbodyStaffId()
    {
        return $this->mindbodyStaffId;
    }
}
