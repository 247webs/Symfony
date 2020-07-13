<?php

namespace AppBundle\Entity;

use AppBundle\Utilities\ConstructorArgs;
use AppBundle\Validator\Constraints as CustomConstraint;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Branch
 * @package AppBundle\Branch
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BranchRepository")
 * @ORM\Table(name="branch")
 * @Hateoas\Relation("self", href="expr('/branch/' ~ object.getId())")
 * @CustomConstraint\UniqueSlugConstraint(
 *     groups={"post", "put", "user_post"}
 * )
 * @CustomConstraint\BranchNameOrIdConstraint(
 *     groups={"post", "user_post"}
 * )
 */
class Branch
{
    use ConstructorArgs;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"public", "private", "list_users"})
     * @Assert\NotBlank(
     *     groups={
     *          "user_put",
     *          "branch_profile_post",
     *          "branch_profile_put",
     *          "feed_setting_post",
     *          "feed_setting_put",
     *          "post_company_access_request",
     *          "put_company_access_request"
     *     }
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Company", inversedBy="branches", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="company", referencedColumnName="id")
     * @Serializer\Groups({"public", "private"})
     * @Assert\Valid()
     */
    private $company;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     * @Serializer\Groups({"public", "private", "list_users"})
     * @Assert\NotBlank(
     *     groups={"put"},
     *     message="Branch slug is required"
     * )
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Serializer\Groups({"public", "private", "list_users"})
     * @Assert\NotBlank(
     *      groups={"post", "branch_post", "put"},
     *      message="Branch name is required"
     * )
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=100,
     *      maxMessage="Branch name cannot exceed {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=100,
     *      maxMessage="Branch address 1 cannot exceed {{ limit }} characters"
     * )
     */
    private $address_1;

    /**
     * @ORM\Column(type="string", length=100, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=100,
     *      maxMessage="Branch address 2 cannot exceed {{ limit }} characters"
     * )
     */
    private $address_2;

    /**
     * @ORM\Column(type="string", length=50, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=50,
     *      maxMessage="Branch city cannot exceed {{ limit }} characters"
     * )
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=2, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=2,
     *     min=2,
     *      exactMessage="Branch state should be exactly {{ limit }} characters"
     * )
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=12, nullable=true, options={"default": null})
     * @Serializer\Groups({"private"})
     * @Assert\Length(
     *      groups={"post", "branch_post", "put", "user_post"},
     *      max=12,
     *      maxMessage="Branch zip cannot exceed {{ limit }} characters"
     * )
     */
    private $zip;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\BranchProfile", mappedBy="branch")
     * @Serializer\Groups({"profile"})
     */
    private $profile;

    /**
     * @Serializer\Groups({"private"})
     * @Serializer\Type("string")
     */
    private $target_plan;

    /**
     * @Serializer\Groups({"private"})
     * @Serializer\Type("string")
     */
    private $update_payment_plan;

    /**
     * @Serializer\Groups({"private"})
     * @Serializer\Type("string")
     */
    private $payee;

    /**
     * @ORM\Column(name="is_rep_reviews_client", type="boolean", nullable=false, options={"default"=false})
     * @Serializer\Groups({"public"})
     * @Serializer\SerializedName("is_rep_reviews_client")
     */
    private $isRepReviewsClient;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReviewAggregationToken\GoogleToken", mappedBy="branch")
     * @Serializer\Groups("private")
     */
    private $googleReviewAggregationToken;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReviewAggregationToken\FacebookToken", mappedBy="branch")
     * @Serializer\Groups("private")
     */
    private $facebookReviewAggregationToken;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken", mappedBy="branch")
     * @Serializer\Groups("private")
     */
    private $zillowNmlsidToken;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\ReviewAggregationToken\ZillowScreenNameToken", mappedBy="branch")
     * @Serializer\Groups("private")
     */
    private $zillowScreenNameToken;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Preferences\BranchPreferences", mappedBy="branch")
     */
    private $preferences;

    /**
     * Get target_plan
     *
     * @return string
     */
    public function getTargetPlan()
    {
        return $this->target_plan;
    }

    /**
     * Set target_plan
     *
     * @param string $target_plan
     *
     * @return Branch
     */
    public function setTargetPlan($targetPlan)
    {
        $this->target_plan= $targetPlan;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatePaymentPlan()
    {
        return $this->update_payment_plan;
    }

    /**
     * @param $updatePaymentPlan
     * @return Branch
     */
    public function setUpdatePaymentPlan($updatePaymentPlan)
    {
        $this->update_payment_plan = $updatePaymentPlan;

        return $this;
    }

    /**
     * Get payee
     *
     * @return string
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * Set payee
     *
     * @param string $payee
     *
     * @return Branch
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;

        return $this;
    }

    /**
     * @ORM\Column(name="active", type="boolean", options={"default": true})
     * @Serializer\Groups({"private"})
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="branch")
     */
    private $users;

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
     * @return Branch
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
     * Set address1
     *
     * @param string $address1
     *
     * @return Branch
     */
    public function setAddress1($address1)
    {
        $this->address_1 = $address1;

        return $this;
    }

    /**
     * Get address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address_1;
    }

    /**
     * Set address2
     *
     * @param string $address2
     *
     * @return Branch
     */
    public function setAddress2($address2)
    {
        $this->address_2 = $address2;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address_2;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Branch
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
     * @return Branch
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
     * Set zip
     *
     * @param string $zip
     *
     * @return Branch
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set active
     *
     * @param bool $active
     *
     * @return Branch
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
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     *
     * @return Branch
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
     * Constructor
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->users = new ArrayCollection();
        $this->handleArgs($args);
    }

    /**
     * Add user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Branch
     */
    public function addUser(\AppBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeUser(\AppBundle\Entity\User $user)
    {
        return $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Branch
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set profile
     *
     * @param \AppBundle\Entity\BranchProfile $profile
     *
     * @return Branch
     */
    public function setProfile(\AppBundle\Entity\BranchProfile $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return \AppBundle\Entity\BranchProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set isRepReviewsClient.
     *
     * @param bool $isRepReviewsClient
     *
     * @return Branch
     */
    public function setIsRepReviewsClient($isRepReviewsClient)
    {
        $this->isRepReviewsClient = $isRepReviewsClient;

        return $this;
    }

    /**
     * Get isRepReviewsClient.
     *
     * @return bool
     */
    public function getIsRepReviewsClient()
    {
        return $this->isRepReviewsClient;
    }

    /**
     * Set googleReviewAggregationToken.
     *
     * @param \AppBundle\Entity\ReviewAggregationToken\GoogleToken|null $googleReviewAggregationToken
     *
     * @return Branch
     */
    public function setGoogleReviewAggregationToken(\AppBundle\Entity\ReviewAggregationToken\GoogleToken $googleReviewAggregationToken = null)
    {
        $this->googleReviewAggregationToken = $googleReviewAggregationToken;

        return $this;
    }

    /**
     * Get googleReviewAggregationToken.
     *
     * @return \AppBundle\Entity\ReviewAggregationToken\GoogleToken|null
     */
    public function getGoogleReviewAggregationToken()
    {
        return $this->googleReviewAggregationToken;
    }

    /**
     * Set facebookReviewAggregationToken.
     *
     * @param \AppBundle\Entity\ReviewAggregationToken\FacebookToken|null $facebookReviewAggregationToken
     *
     * @return User
     */
    public function setFacebookReviewAggregationToken(\AppBundle\Entity\ReviewAggregationToken\FacebookToken $facebookReviewAggregationToken = null)
    {
        $this->facebookReviewAggregationToken = $facebookReviewAggregationToken;

        return $this;
    }

    /**
     * Get facebookReviewAggregationToken.
     *
     * @return \AppBundle\Entity\ReviewAggregationToken\FacebookToken|null
     */
    public function getFacebookReviewAggregationToken()
    {
        return $this->facebookReviewAggregationToken;
    }

    /**
     * Set preferences.
     *
     * @param \AppBundle\Entity\Preferences\BranchPreferences|null $preferences
     *
     * @return Branch
     */
    public function setPreferences(\AppBundle\Entity\Preferences\BranchPreferences $preferences = null)
    {
        $this->preferences = $preferences;

        return $this;
    }

    /**
     * Get preferences.
     *
     * @return \AppBundle\Entity\Preferences\BranchPreferences|null
     */
    public function getPreferences()
    {
        return $this->preferences;
    }

    /**
     * Set zillowNmlsidToken.
     *
     * @param \AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken|null $zillowNmlsidToken
     *
     * @return Branch
     */
    public function setZillowNmlsidToken(\AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken $zillowNmlsidToken = null)
    {
        $this->zillowNmlsidToken = $zillowNmlsidToken;

        return $this;
    }

    /**
     * Get zillowNmlsidToken.
     *
     * @return \AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken|null
     */
    public function getZillowNmlsidToken()
    {
        return $this->zillowNmlsidToken;
    }

    /**
     * Set zillowScreenNameToken.
     *
     * @param \AppBundle\Entity\ReviewAggregationToken\ZillowScreenNameToken|null $zillowScreenNameToken
     *
     * @return Branch
     */
    public function setZillowScreenNameToken(\AppBundle\Entity\ReviewAggregationToken\ZillowScreenNameToken $zillowScreenNameToken = null)
    {
        $this->zillowScreenNameToken = $zillowScreenNameToken;

        return $this;
    }

    /**
     * Get zillowScreenNameToken.
     *
     * @return \AppBundle\Entity\ReviewAggregationToken\ZillowScreenNameToken|null
     */
    public function getZillowScreenNameToken()
    {
        return $this->zillowScreenNameToken;
    }
}
