<?php

namespace AppBundle\Enumeration;

final class PayeeLevel
{
    const COMPANY           = 1;
    const BRANCH            = 2;
    const USER              = 3;

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
