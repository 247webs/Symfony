<?php

namespace AppBundle\Document\Statistic;

interface UserStatisticInterface
{
    public function getId();
    public function setUserId($userId);
    public function getUserId();
    public function getCreationDate();
}