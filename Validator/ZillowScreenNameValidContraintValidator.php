<?php

namespace AppBundle\Validator;

use AppBundle\Services\ReviewAggregation\ZillowService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ZillowScreenNameValidContraintValidator
 * @package AppBundle\Validator
 */
class ZillowScreenNameValidContraintValidator extends ConstraintValidator
{
    /** @var ZillowService $zillowService */
    private $zillowService;

    /**
     * ZillowScreenNameValidContraintValidator constructor.
     * @param ZillowService $zillowService
     */
    public function __construct(ZillowService $zillowService)
    {
        $this->zillowService = $zillowService;
    }

    /**
     * @param mixed $screenName
     * @param Constraint $constraint
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function validate($screenName, Constraint $constraint)
    {
        $reviews = $this->zillowService->getProReviews($screenName);

        if (!$reviews) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%id%', $screenName)
                ->setInvalidValue($screenName)
                ->addViolation();
        }
    }
}
