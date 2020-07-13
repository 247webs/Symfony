<?php

namespace AppBundle\Enumeration;

final class CompanyAccessRequestStatus
{
    const OPEN          = 100;
    const ACCEPTED      = 200;
    const DENIED        = 300;

    /**
     * @return array
     */
    public function getConstants()
    {
        $class = new \ReflectionClass($this);

        return $class->getConstants();
    }
}
