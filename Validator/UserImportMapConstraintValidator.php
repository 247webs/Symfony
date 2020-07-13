<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserImportMapConstraintValidator extends ConstraintValidator
{
    public function validate($map, Constraint $constraint)
    {
        if (!is_array($map)) {
            $this->context->buildViolation("Map can not be decoded")
                ->setInvalidValue($map)
                ->addViolation();
        }

        if (!array_key_exists('username', $map)) {
            $this->context->buildViolation("Map must contain a key for 'username'")
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
    }
}
