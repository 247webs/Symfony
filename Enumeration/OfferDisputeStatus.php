<?php

namespace AppBundle\Enumeration;

final class OfferDisputeStatus
{
    const ACTIVE               = 'active';
    const APPROVED             = 'approved';
    const REJECTED             = 'rejected';

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
