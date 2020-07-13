<?php

namespace AppBundle\Serializer;

use JMS\Serializer\GenericDeserializationVisitor;

/**
 * This class is used by FOSRestBundle when Mailgun sends notifications in application/x-www-form-urlencoded format.
 * By default, the "form" format is not supported by the JMS serializer, but since we can not influence the
 * request format for Mailgun webhooks (only sent in application/x-www-formurlencoded), we must add support for this format.
 */
class FormDeserializationVisitor extends GenericDeserializationVisitor
{
    /**
     * @param $str
     * @return array
     */
    protected function decode($str)
    {
        parse_str($str, $output);

        return $output;
    }
}
