<?php

namespace AppBundle\Validator;

use AppBundle\Services\KickboxService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @package AppBundle\Validator
 */
class KickboxValidOrRiskyValidator extends ConstraintValidator
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
        $valid = $this->kickboxService->isValidOrRisky($value);

        if (false === $valid) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
