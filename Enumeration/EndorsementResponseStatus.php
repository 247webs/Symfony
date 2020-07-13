<?php

namespace AppBundle\Enumeration;

final class EndorsementResponseStatus
{
    const ACTIVE               = 'active';
    const SUPPRESSED           = 'suppressed';
    const DISPUTE_PROGRESS     = 'dispute_in_progress';
    const DISPUTE_APPROVED     = 'dispute_approved';
    const DISPUTE_REJECTED     = 'dispute_rejected';

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
