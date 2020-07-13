<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ContactLabel
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContactLabelRepository")
 * @ORM\Table(name="contact_label")
 */
class ContactLabel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Company")
     * @ORM\JoinColumn(name="company", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Branch")
     * @ORM\JoinColumn(name="branch", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $branch;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public"})
     * @Assert\NotBlank(
     *     groups={"contact_label_post", "contact_label_put"}
     * )
     * @Assert\Length(
     *     groups={"contact_label_post", "contact_label_put"},
     *     max="100"
     * )
     */
    private $contactLabelName;

    /**
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"public"})
     * @Assert\NotBlank(
     *     groups={"contact_label_post", "contact_label_put"}
     * )
     */
    private $displayOrder;

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
     * Set contactLabelName
     *
     * @param string $contactLabelName
     *
     * @return ContactLabel
     */
    public function setContactLabelName($contactLabelName)
    {
        $this->contactLabelName = $contactLabelName;

        return $this;
    }

    /**
     * Get contactLabelName
     *
     * @return string
     */
    public function getContactLabelName()
    {
        return $this->contactLabelName;
    }

    /**
     * Set displayOrder
     *
     * @param string $displayOrder
     *
     * @return ContactLabel
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get displayOrder
     *
     * @return string
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     *
     * @return ContactLabel
     */
    public function setCompany(\AppBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set branch
     *
     * @param \AppBundle\Entity\Branch $branch
     *
     * @return ContactLabel
     */
    public function setBranch(\AppBundle\Entity\Branch $branch = null)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return \AppBundle\Entity\Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return ContactLabel
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
}
