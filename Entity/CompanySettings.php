<?php

namespace AppBundle\Entity;

use AppBundle\Utilities\ConstructorArgs;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CompanySettings
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CompanySettingsRepository")
 * @ORM\Table(name="company_settings")
 * @Hateoas\Relation("self", href="expr('/company-settings/' ~ object.getId())")
 */
class CompanySettings
{
    use ConstructorArgs;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public", "private"})
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Company", inversedBy="settings")
     * @ORM\JoinColumn(name="company", referencedColumnName="id", nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *     groups={"company_settings_post"}
     * )
     * @Assert\Valid()
     */
    private $company;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $suppressUserProfiles;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $overrideProfileDescription;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $overrideFeedWidget;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $overridePushTargets;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $enableGlobalSurvey;


    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Type("boolean")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"company_settings_post", "company_settings_put"}
     * )
     * @Assert\Type(
     *     groups={"company_settings_post", "company_settings_put"},
     *     type="boolean"
     * )
     */
    private $suppressSocialImages;

    /**
     * Constructor
     */
    public function __construct(array $args = [])
    {
        $this->handleArgs($args);
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
     * Set suppressUserProfiles.
     *
     * @param bool $suppressUserProfiles
     *
     * @return CompanySettings
     */
    public function setSuppressUserProfiles($suppressUserProfiles)
    {
        $this->suppressUserProfiles = $suppressUserProfiles;

        return $this;
    }

    /**
     * Get suppressUserProfiles.
     *
     * @return bool
     */
    public function getSuppressUserProfiles()
    {
        return $this->suppressUserProfiles;
    }

    /**
     * Set overrideProfileDescription.
     *
     * @param bool $overrideProfileDescription
     *
     * @return CompanySettings
     */
    public function setOverrideProfileDescription($overrideProfileDescription)
    {
        $this->overrideProfileDescription = $overrideProfileDescription;

        return $this;
    }

    /**
     * Get overrideProfileDescription.
     *
     * @return bool
     */
    public function getOverrideProfileDescription()
    {
        return $this->overrideProfileDescription;
    }

    /**
     * Set overrideFeedWidget.
     *
     * @param bool $overrideFeedWidget
     *
     * @return CompanySettings
     */
    public function setOverrideFeedWidget($overrideFeedWidget)
    {
        $this->overrideFeedWidget = $overrideFeedWidget;

        return $this;
    }

    /**
     * Get overrideFeedWidget.
     *
     * @return bool
     */
    public function getOverrideFeedWidget()
    {
        return $this->overrideFeedWidget;
    }

    /**
     * Set overridePushTargets.
     *
     * @param bool $overridePushTargets
     *
     * @return CompanySettings
     */
    public function setOverridePushTargets($overridePushTargets)
    {
        $this->overridePushTargets = $overridePushTargets;

        return $this;
    }

    /**
     * Get overridePushTargets.
     *
     * @return bool
     */
    public function getOverridePushTargets()
    {
        return $this->overridePushTargets;
    }

    /**
     * Set company.
     *
     * @param \AppBundle\Entity\Company $company
     *
     * @return CompanySettings
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return \AppBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set enableGlobalSurvey.
     *
     * @param bool $enableGlobalSurvey
     *
     * @return CompanySettings
     */
    public function setEnableGlobalSurvey($enableGlobalSurvey)
    {
        $this->enableGlobalSurvey = $enableGlobalSurvey;

        return $this;
    }

    /**
     * Get enableGlobalSurvey.
     *
     * @return bool
     */
    public function getEnableGlobalSurvey()
    {
        return $this->enableGlobalSurvey;
    }

    /**
     * Set suppressSocialImages.
     *
     * @param bool $suppressSocialImages
     *
     * @return CompanySettings
     */
    public function setSuppressSocialImages($suppressSocialImages)
    {
        $this->suppressSocialImages = $suppressSocialImages;

        return $this;
    }

    /**
     * Get suppressSocialImages.
     *
     * @return bool
     */
    public function getSuppressSocialImages()
    {
        return $this->suppressSocialImages;
    }
}
