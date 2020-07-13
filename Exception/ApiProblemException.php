<?php

namespace AppBundle\Exception;

use AppBundle\Model\ApiProblem;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ApiProblemException
 * @package AppBundle\Exception
 */
class ApiProblemException extends HttpException
{
    private $apiProblem;

    /**
     * ApiProblemException constructor.
     * @param ApiProblem $apiProblem
     * @param \Exception $previous
     * @param array      $headers
     * @param int        $code
     */
    public function __construct(ApiProblem $apiProblem, \Exception $previous = null, array $headers = [], $code = 0)
    {
        $this->apiProblem = $apiProblem;

        // get status code
        $statusCode = $apiProblem->getStatusCode();

        // get message
        $message = $apiProblem->getTitle();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    /**
     * @return ApiProblem
     */
    public function getApiProblem()
    {
        return $this->apiProblem;
    }
}
