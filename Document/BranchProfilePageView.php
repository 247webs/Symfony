<?php

namespace AppBundle\Document\Statistic;

use AppBundle\Utilities\ConstructorArgs;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class BranchProfilePageView
 * @package AppBundle\Document\Statistic
 * @ODM\Document(repositoryClass="AppBundle\Repository\Statistic\BranchProfilePageViewRepository")
 */
class BranchProfilePageView implements BranchStatisticInterface
{
    use ConstructorArgs;

    /**
     * @ODM\Id
     * @var int
     */
    private $id;

    /**
     * @ODM\Field(type="int")
     * @var int
     */
    private $branchId;

    /**
     * @ODM\Field(type="date")
     * @var \DateTime
     */
    private $creationDate;

    public function __construct(array $args = [])
    {
        $this->creationDate = new \DateTime;
        $this->handleArgs($args);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getBranchId()
    {
        return $this->branchId;
    }

    /**
     * @param int $branchId
     */
    public function setBranchId($branchId)
    {
        $this->branchId = $branchId;
    }

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
