<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SurveyTypeConstraint extends Constraint
{
    public $message = '"%string%" is not valid survey type';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'survey_type_validator';
    }
}