<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class BranchNameOrIdConstraint extends Constraint
{
    public $message = 'Please provide a Branch Name or the Id of an active branch';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'branch_name_or_id_constraint_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
