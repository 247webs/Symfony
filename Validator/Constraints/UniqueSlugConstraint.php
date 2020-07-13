<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueSlugConstraint extends Constraint
{
    public $message = 'The slug "%slug%" is already in use. Please choose a different slug.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'unique_slug_constraint_validator';
    }

    /**
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
