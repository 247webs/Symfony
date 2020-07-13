<?php

namespace AppBundle\Model;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ApiProblem
 * @package AppBundle\Model
 */
class ApiProblem
{
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';

    private static $titles = [
        self::TYPE_VALIDATION_ERROR => 'Validation error occurred.',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid format sent',
    ];

    private $statusCode;

    private $type;

    private $title;

    private $violations;

    private $errors = [];

    private $detail;

    /**
     * ApiProblem constructor.
     * @param int                              $statusCode
     * @param ConstraintViolationListInterface $violations
     * @param string                           $type
     * @param string                           $detail
     */
    public function __construct(
        $statusCode,
        ConstraintViolationListInterface $violations = null,
        $type = null,
        $detail = null
    ) {
        $this->statusCode = $statusCode;
        $this->violations = $violations;
        $this->detail = $detail;

        // set api problem type and title details
        $this->setProblemDetails($type, $statusCode);

        // process violations
        if (null !== $violations) {
            $this->processViolations($violations);
        }
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return null|string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $problemDetails = [
            'status' => $this->statusCode,
            'type' => $this->type,
            'title' => $this->title,
        ];

        if (null !== $this->detail) {
            $problemDetails['detail'] = $this->detail;
        }

        if (null === $this->violations) {
            return $problemDetails;
        }

        return array_merge(
            $problemDetails,
            ['errors' => $this->errors]
        );
    }

    private function setProblemDetails($type, $statusCode)
    {
        if (null === $type) {
            $type = 'about:blank';
            $title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown status code';
        } else {
            // check if type has title
            if (!isset(self::$titles[$type])) {
                throw new \InvalidArgumentException("No title is set for error type $type.");
            }

            // set title
            $title = self::$titles[$type];
        }

        // set type
        $this->type = $type;

        // set title
        $this->title = $title;
    }


    private function processViolations(ConstraintViolationListInterface $violations)
    {
        // initialize errors array
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $error = [
                'property_path' => $violation->getPropertyPath(),
                'invalid_value' => $violation->getInvalidValue(),
                'message' => $violation->getMessage(),
            ];

            $errors[] = $error;
        }

        $this->errors = $errors;
    }
}
