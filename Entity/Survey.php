<?php

namespace AppBundle\Entity;

use AppBundle\Utilities\ConstructorArgs;
use AppBundle\Validator\Constraints as CustomConstraint;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Survey
 * @package AppBundle\Survey
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SurveyRepository")
 * @ORM\Table(name="survey")
 * @Hateoas\Relation("self", href="expr('/survey/' ~ object.getId())")
 * @CustomConstraint\SurveyPushUrlConstraint(
 *     groups={"survey_post", "survey_put"}
 * )
 */
class Survey
{
    use ConstructorArgs;

    const SURVEY_TYPE_BASIC         = 'basic';
    const SURVEY_TYPE_CUSTOM        = 'custom';
    const SURVEY_TYPE_REVIEW_PUSH   = 'review push';
    const SURVEY_TYPE_VIDEOMONIAL   = 'videomonial';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *     groups={
     *          "scheduled_event_post",
     *          "scheduled_event_put"
     *      }
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="surveys", cascade={"all"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     * @Serializer\Groups({"private", "public"})
     * @Assert\NotBlank(
     *     groups={"survey_post"}
     * )
     * @Assert\Blank(
     *     groups={"wix_survey_post"}
     * )
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Wix\WixUser")
     * @ORM\JoinColumn(name="wix_user", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"wix_survey_post"}
     * )
     * @Assert\Valid()
     */
    private $wix_user;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Survey name is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Survey name cannot exceed {{ limit }} characters"
     * )
     */
    private $survey_name;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Survey subject line is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Survey subject line cannot exceed {{ limit }} characters"
     * )
     */
    private $survey_subject_line;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Survey greeting is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Survey greeting cannot exceed {{ limit }} characters"
     * )
     */
    private $survey_greeting;

    /**
     * @ORM\Column(type="text", nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Survey message is required"
     * )
     */
    private $survey_message;
 
    /**
     * @ORM\Column(type="string", length=50, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=50,
     *      maxMessage="Ignore button text cannot exceed {{ limit }} characters"
     * )
     */
    private $ignore_button_text;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"public", "private"})
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Survey link label text cannot exceed {{ limit }} characters"
     * )
     */
    private $survey_link_label;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"public", "private"})
     */
    private $survey_link_color;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Groups({"public", "private"})
     */
    private $survey_link_text_color;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Survey sign off is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Survey sign off cannot exceed {{ limit }} characters"
     * )
     */
    private $survey_sign_off;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Merchant first name is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=50,
     *      maxMessage="Merchant first name cannot exceed {{ limit }} characters"
     * )
     */
    private $merchant_first_name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=50,
     *      maxMessage="Merchant last name cannot exceed {{ limit }} characters"
     * )
     */
    private $merchant_last_name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=50,
     *      maxMessage="Merchant title cannot exceed {{ limit }} characters"
     * )
     */
    private $merchant_title;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      message="Merchant e-mail address is required"
     * )
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=100,
     *      maxMessage="Merchant e-mail address cannot exceed {{ limit }} characters"
     * )
     * @Assert\Email(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      checkMX=true,
     *      checkHost=true,
     *      message="Merchant e-mail address must be a valid e-mail address"
     * )
     */
    private $merchant_email_address;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      max=20,
     *      maxMessage="Merchant phone number cannot exceed {{ limit }} characters"
     * )
     */
    private $merchant_phone;

    /**
     * @ORM\Column(name="active", type="boolean", options={"default": true})
     * @Serializer\Groups({"public", "private"})
     * @Assert\NotNull(
     *     groups={"survey_put", "wix_survey_put"}
     * )
     * @Assert\Type(
     *      groups={"survey_put", "wix_survey_put"},
     *      type="boolean"
     * )
     */
    private $active;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", nullable=false, options={"default": false})
     * @Serializer\Groups({"public", "private"})
     * @Assert\Type(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      type="boolean"
     * )
     */
    private $isDefault;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=false, options={"default": "basic"})
     * @Serializer\Groups({"public", "private"})
     * @CustomConstraint\SurveyTypeConstraint(
     *     groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"}
     * )
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_global_survey", type="boolean", nullable=false, options={"default": false})
     * @Serializer\Groups({"public", "private"})
     * @Assert\Type(
     *      groups={"survey_post", "survey_put", "wix_survey_post", "wix_survey_put"},
     *      type="boolean"
     * )
     */
    private $isGlobalSurvey;

    /**
     * @var SurveyPushUrl
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyPushUrl", mappedBy="survey", cascade={"all"})
     * @Serializer\Groups({"public", "private"})
     * @Assert\Valid()
     * @Serializer\Type("ArrayCollection<AppBundle\Entity\SurveyPushUrl>")
     */
    private $pushUrls;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\SurveyQuestion", mappedBy="survey", cascade={"all"})
     * @Serializer\Groups({"public", "private"})
     * @Assert\Valid()
     * @Serializer\Type("ArrayCollection<AppBundle\Entity\SurveyQuestion>")
     */
    private $survey_questions;

    /**
     * Constructor
     */
    public function __construct(array $args = [])
    {
        $this->survey_questions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set surveyName
     *
     * @param string $surveyName
     *
     * @return Survey
     */
    public function setSurveyName($surveyName)
    {
        $this->survey_name = $surveyName;

        return $this;
    }

    /**
     * Get surveyName
     *
     * @return string
     */
    public function getSurveyName()
    {
        return $this->survey_name;
    }

    /**
     * Set surveySubjectLine
     *
     * @param string $surveySubjectLine
     *
     * @return Survey
     */
    public function setSurveySubjectLine($surveySubjectLine)
    {
        $this->survey_subject_line = $surveySubjectLine;

        return $this;
    }

    /**
     * Get surveySubjectLine
     *
     * @return string
     */
    public function getSurveySubjectLine()
    {
        return $this->survey_subject_line;
    }

    /**
     * Set surveyGreeting
     *
     * @param string $surveyGreeting
     *
     * @return Survey
     */
    public function setSurveyGreeting($surveyGreeting)
    {
        $this->survey_greeting = $surveyGreeting;

        return $this;
    }

    /**
     * Get surveyGreeting
     *
     * @return string
     */
    public function getSurveyGreeting()
    {
        return $this->survey_greeting;
    }

    /**
     * Set surveyMessage
     *
     * @param string $surveyMessage
     *
     * @return Survey
     */
    public function setSurveyMessage($surveyMessage)
    {
        $this->survey_message = $surveyMessage;

        return $this;
    }

    /**
     * Get surveyMessage
     *
     * @return string
     */
    public function getSurveyMessage()
    {
        return $this->survey_message;
    }

    /**
     * Set ignoreButtonText   
     *
     * @param string $ignoreButtonText
     *
     * @return Survey
     */
    public function setIgnoreButtonText($ignoreButtonText)
    {
        $this->ignore_button_text = $ignoreButtonText;

        return $this;
    }

    /**
     * Get ignoreButtonText
     *
     * @return string
     */
    public function getIgnoreButtonText()
    {
        return $this->ignore_button_text;
    }
 
    /**
     * Set surveyLinkLabel
     *
     * @param string $surveyLinkLabel
     *
     * @return Survey
     */
    public function setSurveyLinkLabel($surveyLinkLabel)
    {
        $this->survey_link_label = $surveyLinkLabel;

        return $this;
    }

    /**
     * Get surveyLinkLabel
     *
     * @return string
     */
    public function getSurveyLinkLabel()
    {
        return $this->survey_link_label;
    }

    /**
     * Set surveyLinkColor
     *
     * @param string $surveyLinkColor
     *
     * @return Survey
     */
    public function setSurveyLinkColor($surveyLinkColor)
    {
        $this->survey_link_color = $surveyLinkColor;

        return $this;
    }

    /**
     * Get surveyLinkColor
     *
     * @return string
     */
    public function getSurveyLinkColor()
    {
        return $this->survey_link_color;
    }

    /**
     * Set surveyLinkTextColor
     *
     * @param string $surveyLinkTextColor
     *
     * @return Survey
     */
    public function setSurveyLinkTextColor($surveyLinkTextColor)
    {
        $this->survey_link_text_color = $surveyLinkTextColor;

        return $this;
    }

    /**
     * Get surveyLinkTextColor
     *
     * @return string
     */
    public function getSurveyLinkTextColor()
    {
        return $this->survey_link_text_color;
    }

    /**
     * Set surveySignOff
     *
     * @param string $surveySignOff
     *
     * @return Survey
     */
    public function setSurveySignOff($surveySignOff)
    {
        $this->survey_sign_off = $surveySignOff;

        return $this;
    }

    /**
     * Get surveySignOff
     *
     * @return string
     */
    public function getSurveySignOff()
    {
        return $this->survey_sign_off;
    }

    /**
     * Set merchantFirstName
     *
     * @param string $merchantFirstName
     *
     * @return Survey
     */
    public function setMerchantFirstName($merchantFirstName)
    {
        $this->merchant_first_name = $merchantFirstName;

        return $this;
    }

    /**
     * Get merchantFirstName
     *
     * @return string
     */
    public function getMerchantFirstName()
    {
        return $this->merchant_first_name;
    }

    /**
     * Set merchantLastName
     *
     * @param string $merchantLastName
     *
     * @return Survey
     */
    public function setMerchantLastName($merchantLastName)
    {
        $this->merchant_last_name = $merchantLastName;

        return $this;
    }

    /**
     * Get merchantLastName
     *
     * @return string
     */
    public function getMerchantLastName()
    {
        return $this->merchant_last_name;
    }

    /**
     * Set merchantTitle
     *
     * @param string $merchantTitle
     *
     * @return Survey
     */
    public function setMerchantTitle($merchantTitle)
    {
        $this->merchant_title = $merchantTitle;

        return $this;
    }

    /**
     * Get merchantTitle
     *
     * @return string
     */
    public function getMerchantTitle()
    {
        return $this->merchant_title;
    }

    /**
     * Set merchantEmailAddress
     *
     * @param string $merchantEmailAddress
     *
     * @return Survey
     */
    public function setMerchantEmailAddress($merchantEmailAddress)
    {
        $this->merchant_email_address = $merchantEmailAddress;

        return $this;
    }

    /**
     * Get merchantEmailAddress
     *
     * @return string
     */
    public function getMerchantEmailAddress()
    {
        return $this->merchant_email_address;
    }

    /**
     * Set merchantPhone
     *
     * @param string $merchantPhone
     *
     * @return Survey
     */
    public function setMerchantPhone($merchantPhone)
    {
        $this->merchant_phone = $merchantPhone;

        return $this;
    }

    /**
     * Get merchantPhone
     *
     * @return string
     */
    public function getMerchantPhone()
    {
        return $this->merchant_phone;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return Survey
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
     * @return Survey
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
     * Add surveyQuestion
     *
     * @param \AppBundle\Entity\SurveyQuestion $surveyQuestion
     *
     * @return Survey
     */
    public function addSurveyQuestion(\AppBundle\Entity\SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->setSurvey($this);

        $this->survey_questions[] = $surveyQuestion;

        return $this;
    }

    /**
     * Remove surveyQuestion
     *
     * @param \AppBundle\Entity\SurveyQuestion $surveyQuestion
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeSurveyQuestion(\AppBundle\Entity\SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->setSurvey(null);

        return $this->survey_questions->removeElement($surveyQuestion);
    }

    /**
     * @param Collection $surveyQuestions
     */
    public function setSurveyQuestions(Collection $surveyQuestions)
    {
        $this->survey_questions = $surveyQuestions;
    }

    /**
     * Get surveyQuestions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSurveyQuestions()
    {
        return $this->survey_questions;
    }

    /**
     * Set wixUser
     *
     * @param \AppBundle\Entity\Wix\WixUser $wixUser
     *
     * @return Survey
     */
    public function setWixUser(\AppBundle\Entity\Wix\WixUser $wixUser = null)
    {
        $this->wix_user = $wixUser;

        return $this;
    }

    /**
     * Get wixUser
     *
     * @return \AppBundle\Entity\Wix\WixUser
     */
    public function getWixUser()
    {
        return $this->wix_user;
    }

    /**
     * Set isDefault.
     *
     * @param bool $isDefault
     *
     * @return Survey
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault.
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Survey
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set isGlobalSurvey.
     *
     * @param bool $isGlobalSurvey
     *
     * @return Survey
     */
    public function setIsGlobalSurvey($isGlobalSurvey)
    {
        $this->isGlobalSurvey = $isGlobalSurvey;

        return $this;
    }

    /**
     * Get isGlobalSurvey.
     *
     * @return bool
     */
    public function getIsGlobalSurvey()
    {
        return $this->isGlobalSurvey;
    }

    /**
     * Add pushUrl.
     *
     * @param \AppBundle\Entity\SurveyPushUrl $pushUrl
     *
     * @return Survey
     */
    public function addPushUrl(\AppBundle\Entity\SurveyPushUrl $pushUrl)
    {
        $this->pushUrls[] = $pushUrl;

        return $this;
    }

    /**
     * Remove pushUrl.
     *
     * @param \AppBundle\Entity\SurveyPushUrl $pushUrl
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removePushUrl(\AppBundle\Entity\SurveyPushUrl $pushUrl)
    {
        return $this->pushUrls->removeElement($pushUrl);
    }

    /**
     * Get pushUrls.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPushUrls()
    {
        return $this->pushUrls;
    }
}
