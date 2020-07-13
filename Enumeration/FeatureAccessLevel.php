<?php

namespace AppBundle\Enumeration;

final class FeatureAccessLevel
{
    const FREE                  = '100';
    const BASIC_MONTHLY         = '200';
    const BASIC_ANNUAL          = '200';
    const PREMIUM_MONTHLY       = '300';
    const PREMIUM_ANNUAL        = '300';
    const PREMIUM_PLUS_MONTHLY  = '400';
    const PREMIUM_PLUS_ANNUAL   = '400';
    const ENTERPRISE            = '500';
    const RESELLER              = '500';

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
