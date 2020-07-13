<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DrchronoDoctor
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DrchronoDoctorRepository")
 * @ORM\Table(name="drchrono_doctor")
 */
class DrchronoDoctor
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"private"})
     * @Assert\Blank(
     *     groups={"drchrono_doctor_post", "drchrono_doctor_put"}
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\DrchronoPractice")
     * @ORM\JoinColumn(name="drchrono_practice_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     */
    private $drchronoPractice;

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
     * @ORM\Column(type="string", length=200, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"drchrono_doctor_post", "drchrono_doctor_put"},
     *     message="Drchrono doctor Id is required"
     * )
     */
    private $drchronoDoctorId;

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
     * Set drchronoPractice.
     *
     * @param \AppBundle\Entity\DrchronoPractice $drchronoPractice
     *
     * @return DrchronoDoctor
     */
    public function setDrchronoPractice(\AppBundle\Entity\DrchronoPractice $drchronoPractice = null)
    {
        $this->drchronoPractice = $drchronoPractice;

        return $this;
    }

    /**
     * Get drchronoPractice.
     *
     * @return \AppBundle\Entity\DrchronoPractice|null
     */
    public function getDrchronoPractice()
    {
        return $this->drchronoPractice;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return DrchronoDoctor
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
     * Set drchronoDoctorId.
     *
     * @param $drchronoDoctorId
     *
     * @return DrchronoDoctor
     */
    public function setDrchronoDoctorId($drchronoDoctorId = null)
    {
        $this->drchronoDoctorId = $drchronoDoctorId;

        return $this;
    }

    /**
     * Get drchronoDoctorId.
     *
     * @return Integer
     */
    public function getDrchronoDoctorId()
    {
        return $this->drchronoDoctorId;
    }
}