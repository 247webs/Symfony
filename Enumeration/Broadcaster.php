<?php

namespace AppBundle\Enumeration;

final class Broadcaster
{
    const FACEBOOK                  = "facebook";
    const TWITTER                   = "twitter";
    const GOOGLEPLUS                = "googleplus";
    const LINKEDIN                  = "linkedin";

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}

