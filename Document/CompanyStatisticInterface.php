<?php

namespace AppBundle\Document\Statistic;

interface CompanyStatisticInterface
{
    public function getId();
    public function setCompanyId($companyId);
    public function getCompanyId();
    public function getCreationDate();
}