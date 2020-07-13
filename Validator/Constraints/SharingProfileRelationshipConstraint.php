<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SharingProfileRelationshipConstraint extends Constraint
{
    public $message = 'Sharing profiles must relate to one and only one user, branch or company';

    /**
     * @return string
     */
    public function validatedBy()
    {
        return 'sharing_profile_relationship_constraint_validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
