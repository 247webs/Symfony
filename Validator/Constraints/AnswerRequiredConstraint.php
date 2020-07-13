<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AnswerRequiredConstraint extends Constraint
{
    public $message = 'An answer to this question is required';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'answer_required_constraint_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
