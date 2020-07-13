<?php

namespace AppBundle\Validator;

use AppBundle\Services\KickboxService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @package AppBundle\Validator
 */
class KickboxApprovedValidator extends ConstraintValidator
{
    /**
     * @var KickboxService
     */
    private $kickboxService;

    /**
     * @param KickboxService $kickboxService
     */
    public function __construct(KickboxService $kickboxService)
    {
        $this->kickboxService = $kickboxService;
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        $valid = $this->kickboxService->isValid($value);

        if (false === $valid) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
