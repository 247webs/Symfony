<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AthenaProvider
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AthenaProvider")
 * @ORM\Table(name="athena_provider")
 */
class AthenaProvider
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"private"})
     * @Assert\Blank(
     *     groups={"athena_provider_post", "athena_provider_put"}
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AthenaPractice")
     * @ORM\JoinColumn(name="athena_practice_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"athena_provider_post", "athena_provider_put"}
     * )
     */
    private $athenaPractice;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"athena_provider_post", "athena_provider_put"}
     * )
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"athena_provider_post", "athena_provider_put"},
     *     message="Athena Provider ID is required"
     * )
     */
    private $athenaProviderId;

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
     * Set athenaPractice.
     *
     * @param \AppBundle\Entity\AthenaPractice|null $athenaPractice
     *
     * @return AthenaProvider
     */
    public function setAthenaPractice(\AppBundle\Entity\AthenaPractice $athenaPractice = null)
    {
        $this->athenaPractice = $athenaPractice;

        return $this;
    }

    /**
     * Get athenaPractice.
     *
     * @return \AppBundle\Entity\AthenaPractice|null
     */
    public function getAthenaPractice()
    {
        return $this->athenaPractice;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User|null $user
     *
     * @return AthenaProvider
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
     * Set athenaProviderId.
     *
     * @param string $athenaProviderId
     *
     * @return AthenaProvider
     */
    public function setAthenaProviderId($athenaProviderId)
    {
        $this->athenaProviderId = $athenaProviderId;

        return $this;
    }

    /**
     * Get athenaProviderId.
     *
     * @return string
     */
    public function getAthenaProviderId()
    {
        return $this->athenaProviderId;
    }
}
