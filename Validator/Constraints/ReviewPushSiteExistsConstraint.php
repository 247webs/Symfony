<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ReviewPushSiteExistsConstraint extends Constraint
{
    public $message = 'The Id \"%string%\" is not valid';

    public function validatedBy()
    {
        return 'review_push_site_exists_constraint_validator';
    }
}
