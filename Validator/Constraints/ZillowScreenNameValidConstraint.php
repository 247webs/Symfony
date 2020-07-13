<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ZillowScreenNameValidConstraint
 * @package AppBundle\Validator\Constraints
 *
 * @Annotation
 */
class ZillowScreenNameValidConstraint extends Constraint
{
    public $message = 'Zillow does not recognize %id% as a valid screen name';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'zillow_screen_name_valid_constraint_validator';
    }
}
