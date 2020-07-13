<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Industry
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IndustryRepository")
 * @ORM\Table(name="industry")
 * @Hateoas\Relation("self", href="expr('/industry/' ~ object.getId())")
 */
class Industry
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"default", "public", "private"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"default", "public", "private"})
     * @Assert\NotBlank(
     *     groups={"post", "put"}
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default": 0})
     * @Serializer\Groups({"default", "public", "private"})
     * @Assert\NotNull(
     *     groups={"post", "put"},
     *     message="Sort is required"
     * )
     * @Assert\Type(
     *     groups={"post", "put"},
     *     type="integer",
     *     message="Sort must be an {{ type }}"
     * )
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": true})
     * @Serializer\Groups({"default", "public", "private"})
     * @Assert\NotNull(
     *     groups={"put"},
     *     message="Active flag is required"
     * )
     * @Assert\Type(
     *     groups={"put"},
     *     type="boolean",
     *     message="Active flag must be either true or false"
     * )
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
     * Set name
     *
     * @param string $name
     *
     * @return Industry
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
     * Set sort
     *
     * @param int $sort
     *
     * @return Industry
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort
     *
     * @return int
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return Industry
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
