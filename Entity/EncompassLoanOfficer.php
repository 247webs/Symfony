<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class EncompassLoanOfficer
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EncompassLoanOfficerRepository")
 * @ORM\Table(name="encompass_loan_officer")
 */
class EncompassLoanOfficer
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"private"})
     * @Assert\Blank(
     *     groups={"encompass_loan_officer_post", "encompass_loan_officer_put"}
     * )
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EncompassToken")
     * @ORM\JoinColumn(name="encompass_token_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"encompass_loan_officer_post", "encompass_loan_officer_put"}
     * )
     */
    private $encompassToken;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Serializer\Groups({"private"})
     * @Assert\Valid()
     * @Assert\NotBlank(
     *     groups={"encompass_loan_officer_post", "encompass_loan_officer_put"}
     * )
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Serializer\Groups({"private"})
     * @Assert\NotBlank(
     *     groups={"encompass_loan_officer_post", "encompass_loan_officer_put"},
     *     message="Loan Officer ID is required"
     * )
     */
    private $loanOfficerId;

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
     * Set encompassToken.
     *
     * @param \AppBundle\Entity\EncompassToken|null $encompassToken
     *
     * @return EncompassLoanOfficer
     */
    public function setEncompassToken(\AppBundle\Entity\EncompassToken $encompassToken = null)
    {
        $this->encompassToken = $encompassToken;

        return $this;
    }

    /**
     * Get encompassToken.
     *
     * @return \AppBundle\Entity\EncompassToken|null
     */
    public function getEncompassToken()
    {
        return $this->encompassToken;
    }

    /**
     * Set user.
     *
     * @param \AppBundle\Entity\User|null $user
     *
     * @return EncompassLoanOfficer
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
     * Set loanOfficerId.
     *
     * @param string $loanOfficerId
     *
     * @return EncompassLoanOfficer
     */
    public function setLoanOfficerId($loanOfficerId)
    {
        $this->loanOfficerId = $loanOfficerId;

        return $this;
    }

    /**
     * Get loanOfficerId.
     *
     * @return string
     */
    public function getLoanOfficerId()
    {
        return $this->loanOfficerId;
    }
}
