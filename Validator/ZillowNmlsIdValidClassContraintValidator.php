<?php

namespace AppBundle\Validator;

use AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken;
use AppBundle\Services\ReviewAggregation\ZillowService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ZillowNmlsIdValidClassContraintValidator
 * @package AppBundle\Validator
 */
class ZillowNmlsIdValidClassContraintValidator extends ConstraintValidator
{
    /** @var ZillowService $zillowService */
    private $zillowService;

    /**
     * ZillowNmlsIdValidContraintValidator constructor.
     * @param ZillowService $zillowService
     */
    public function __construct(ZillowService $zillowService)
    {
        $this->zillowService = $zillowService;
    }

    /**
     * @param ZillowNmlsidToken $zillowNmlsidToken
     * @param Constraint $constraint
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validate($zillowNmlsidToken, Constraint $constraint)
    {
        if (null === $zillowNmlsidToken->getNmlsId()) {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($zillowNmlsidToken->getNmlsId())
                ->atPath('nmlsId')
                ->addViolation();

            return;
        }

        $reviews = $this->zillowService->getLenderReviews(
            $zillowNmlsidToken->getNmlsId(),
            $zillowNmlsidToken->getCompanyName()
        );

        if (!$reviews) {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($zillowNmlsidToken->getNmlsId())
                ->atPath('nmlsId')
                ->addViolation();
        }
    }
}
