<?php

namespace AppBundle\Enumeration;

final class FeedWidgetStyle
{
    const MARQUEE           = 'marquee';
    const SLIDER            = 'slider';

    public function getConstants()
    {
        $class = new \ReflectionClass(__CLASS__);
        return $class->getConstants();
    }
}
