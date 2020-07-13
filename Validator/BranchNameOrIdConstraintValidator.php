<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Branch;
use AppBundle\Repository\BranchRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class BranchNameOrIdConstraintValidator
 * @package AppBundle\Validator
 */
class BranchNameOrIdConstraintValidator extends ConstraintValidator
{
    private $branchRepository;

    /**
     * BranchNameOrIdConstraintValidator constructor.
     * @param BranchRepository $branchRepository
     */
    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    /**
     * @param Branch     $branch
     * @param Constraint $constraint
     */
    public function validate($branch, Constraint $constraint)
    {
        if (null !== $branch->getId()) {
            if (!$this->branchRepository->find($branch->getId())) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('id')
                    ->setParameter('%id%', $branch->getId())
                    ->setInvalidValue($branch->getId())
                    ->addViolation();
            }
        } else {
            if (null == $branch->getName()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('name')
                    ->setParameter('%name%', $branch->getName())
                    ->setInvalidValue($branch->getName())
                    ->addViolation();
            }
        }
    }
}
