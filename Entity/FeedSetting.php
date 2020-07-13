<?php

namespace AppBundle\Entity;

use AppBundle\Validator\Constraints as CustomConstraint;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FeedSetting
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FeedSettingRepository")
 * @ORM\Table(name="feed_setting")
 * @Hateoas\Relation("self", href="expr('/feed-setting/' ~ object.getId())")
 * @CustomConstraint\FeedRelationshipConstraint(
 *     groups={"feet_setting_post", "feet_setting_put"}
 * )
 */
class FeedSetting
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
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Branch")
     * @ORM\JoinColumn(name="branch_id", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $branch;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"public"})
     * @Assert\Valid()
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=20, nullable=false, options={"default": "marquee"})
     * @Serializer\Groups({"public"})
     * @Assert\NotBlank(
     *     groups={"feet_setting_post", "feet_setting_put"}
     * )
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $style;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @Serializer\Groups({"public"})
     * @Assert\NotBlank(
     *     groups={"feet_setting_post", "feet_setting_put"}
     * )
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $showReplies;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $background_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $border_color;

    /**
     * @ORM\Column(type="string", length=10, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="10"
     * )
     */
    private $border_width;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $header_background_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $header_text_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $header_star_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $endorsement_source_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $endorsement_text_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $endorsement_reply_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $endorsement_star_color;

    /**
     * @ORM\Column(type="string", length=20, nullable=true, options={"default": null})
     * @Serializer\Groups({"public"})
     * @Assert\Length(
     *     groups={"feet_setting_post", "feet_setting_put"},
     *     max="20"
     * )
     */
    private $footer_background_color;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default": 3})
     * @Serializer\Groups({"public"})
     * @Assert\NotNull(
     *     groups={"feet_setting_post", "feet_setting_put"}
     * )
     */
    private $minimum_review_value;


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
     * Set style
     *
     * @param string $style
     *
     * @return FeedSetting
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set backgroundColor
     *
     * @param string $backgroundColor
     *
     * @return FeedSetting
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->background_color = $backgroundColor;

        return $this;
    }

    /**
     * Get backgroundColor
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->background_color;
    }

    /**
     * Set borderColor
     *
     * @param string $borderColor
     *
     * @return FeedSetting
     */
    public function setBorderColor($borderColor)
    {
        $this->border_color = $borderColor;

        return $this;
    }

    /**
     * Get borderColor
     *
     * @return string
     */
    public function getBorderColor()
    {
        return $this->border_color;
    }

    /**
     * Set borderWidth
     *
     * @param string $borderWidth
     *
     * @return FeedSetting
     */
    public function setBorderWidth($borderWidth)
    {
        $this->border_width = $borderWidth;

        return $this;
    }

    /**
     * Get borderWidth
     *
     * @return string
     */
    public function getBorderWidth()
    {
        return $this->border_width;
    }

    /**
     * Set headerBackgroundColor
     *
     * @param string $headerBackgroundColor
     *
     * @return FeedSetting
     */
    public function setHeaderBackgroundColor($headerBackgroundColor)
    {
        $this->header_background_color = $headerBackgroundColor;

        return $this;
    }

    /**
     * Get headerBackgroundColor
     *
     * @return string
     */
    public function getHeaderBackgroundColor()
    {
        return $this->header_background_color;
    }

    /**
     * Set headerTextColor
     *
     * @param string $headerTextColor
     *
     * @return FeedSetting
     */
    public function setHeaderTextColor($headerTextColor)
    {
        $this->header_text_color = $headerTextColor;

        return $this;
    }

    /**
     * Get headerTextColor
     *
     * @return string
     */
    public function getHeaderTextColor()
    {
        return $this->header_text_color;
    }

    /**
     * Set headerStarColor
     *
     * @param string $headerStarColor
     *
     * @return FeedSetting
     */
    public function setHeaderStarColor($headerStarColor)
    {
        $this->header_star_color = $headerStarColor;

        return $this;
    }

    /**
     * Get headerStarColor
     *
     * @return string
     */
    public function getHeaderStarColor()
    {
        return $this->header_star_color;
    }

    /**
     * Set endorsementSourceColor
     *
     * @param string $endorsementSourceColor
     *
     * @return FeedSetting
     */
    public function setEndorsementSourceColor($endorsementSourceColor)
    {
        $this->endorsement_source_color = $endorsementSourceColor;

        return $this;
    }

    /**
     * Get endorsementSourceColor
     *
     * @return string
     */
    public function getEndorsementSourceColor()
    {
        return $this->endorsement_source_color;
    }

    /**
     * Set endorsementTextColor
     *
     * @param string $endorsementTextColor
     *
     * @return FeedSetting
     */
    public function setEndorsementTextColor($endorsementTextColor)
    {
        $this->endorsement_text_color = $endorsementTextColor;

        return $this;
    }

    /**
     * Get endorsementTextColor
     *
     * @return string
     */
    public function getEndorsementTextColor()
    {
        return $this->endorsement_text_color;
    }

    /**
     * Set endorsementStarColor
     *
     * @param string $endorsementStarColor
     *
     * @return FeedSetting
     */
    public function setEndorsementStarColor($endorsementStarColor)
    {
        $this->endorsement_star_color = $endorsementStarColor;

        return $this;
    }

    /**
     * Get endorsementStarColor
     *
     * @return string
     */
    public function getEndorsementStarColor()
    {
        return $this->endorsement_star_color;
    }

    /**
     * Set footerBackgroundColor
     *
     * @param string $footerBackgroundColor
     *
     * @return FeedSetting
     */
    public function setFooterBackgroundColor($footerBackgroundColor)
    {
        $this->footer_background_color = $footerBackgroundColor;

        return $this;
    }

    /**
     * Get footerBackgroundColor
     *
     * @return string
     */
    public function getFooterBackgroundColor()
    {
        return $this->footer_background_color;
    }

    /**
     * Set minimumReviewValue
     *
     * @param int $minimumReviewValue
     *
     * @return FeedSetting
     */
    public function setMinimumReviewValue($minimumReviewValue)
    {
        $this->minimum_review_value = $minimumReviewValue;

        return $this;
    }

    /**
     * Get minimumReviewValue
     *
     * @return int
     */
    public function getMinimumReviewValue()
    {
        return $this->minimum_review_value;
    }

    /**
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     *
     * @return FeedSetting
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
     * @return FeedSetting
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
     * @return FeedSetting
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
     * Set showReplies.
     *
     * @param bool $showReplies
     *
     * @return FeedSetting
     */
    public function setShowReplies($showReplies)
    {
        $this->showReplies = $showReplies;

        return $this;
    }

    /**
     * Get showReplies.
     *
     * @return bool
     */
    public function getShowReplies()
    {
        return $this->showReplies;
    }

    /**
     * Set endorsementReplyColor.
     *
     * @param string|null $endorsementReplyColor
     *
     * @return FeedSetting
     */
    public function setEndorsementReplyColor($endorsementReplyColor = null)
    {
        $this->endorsement_reply_color = $endorsementReplyColor;

        return $this;
    }

    /**
     * Get endorsementReplyColor.
     *
     * @return string|null
     */
    public function getEndorsementReplyColor()
    {
        return $this->endorsement_reply_color;
    }
}
