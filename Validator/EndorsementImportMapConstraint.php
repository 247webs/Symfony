<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class EndorsementImportMapConstraint
 * @package AppBundle\Validator
 */
class EndorsementImportMapConstraint extends ConstraintValidator
{
    /**
     * @param mixed $map
     * @param Constraint $constraint
     */
    public function validate($map, Constraint $constraint)
    {
        /**
         * Sample Map
         {"first_name": "First Name", "last_name": "Last Name", "email": "Email", "city": "City",
         "state": "State", "can_share": "Can Share", "submitted": "Submitted", "rating": "Rating",
         "comments": "Comments", "survey_id": "Survey Id"}
         */

        if (!is_array($map)) {
            $this->context->buildViolation("Map can not be decoded")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('first_name', $map)) {
            $this->context->buildViolation("Map must contain a key for 'first_name'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('last_name', $map)) {
            $this->context->buildViolation("Map must contain a key for 'last_name'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('email', $map)) {
            $this->context->buildViolation("Map must contain a key for 'email'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('city', $map)) {
            $this->context->buildViolation("Map must contain a key for 'city'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('state', $map)) {
            $this->context->buildViolation("Map must contain a key for 'state'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('can_share', $map)) {
            $this->context->buildViolation("Map must contain a key for 'can_share'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('submitted', $map)) {
            $this->context->buildViolation("Map must contain a key for 'submitted'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('rating', $map)) {
            $this->context->buildViolation("Map must contain a key for 'rating'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('comments', $map)) {
            $this->context->buildViolation("Map must contain a key for 'comments'")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('survey_id', $map)) {
            $this->context->buildViolation("Map must contain a key for 'survey_id'")
                ->setInvalidValue($map)
                ->addViolation();
        }
    }
}
