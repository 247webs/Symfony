<?php

namespace AppBundle\Document\Statistic;

interface BranchStatisticInterface
{
    public function getId();
    public function setBranchId($branchId);
    public function getBranchId();
    public function getCreationDate();
}