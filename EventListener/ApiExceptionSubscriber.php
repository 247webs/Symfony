<?php

namespace AppBundle\EventListener;

use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ApiExceptionSubscriber
 * @package AppBundle\EventListener
 */
class ApiExceptionSubscriber implements EventSubscriberInterface
{
    private $debug;

    /**
     * ApiExceptionSubscriber constructor.
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['processException', 10],
            ],
        ];
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function processException(GetResponseForExceptionEvent $event)
    {
        // get exception
        $exception = $event->getException();

        // get status code
        $statusCode = ($exception instanceof HttpExceptionInterface)
            ? $exception->getStatusCode()
            : 500;

        // show additional error information for debugging
        if (500 == $statusCode && $this->debug) {
            return;
        }

        if ($exception instanceof ApiProblemException) {
            $apiProblem = $exception->getApiProblem();
        } else {
            // set details
            $detail = ($exception instanceof HttpExceptionInterface)
                ? $exception->getMessage()
                : null;

            $apiProblem = new ApiProblem($statusCode, null, null, $detail);
        }

        $response = new JsonResponse(
            $apiProblem->toArray(),
            $apiProblem->getStatusCode(),
            ['Content-Type' => 'application/problem+json']
        );

        $event->setResponse($response);
    }
}
