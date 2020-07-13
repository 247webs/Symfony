<?php

namespace AppBundle\Controller;

use AppBundle\Document\EndorsementRequest;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @Route("/twilio/event") */
class TwilioEventController extends FOSRestController
{
    private $allowedEvents = [
        'delivered',
        'failed'
    ];

    /**
     * @Rest\Post(path="/{id}", name="twilio_callback_post")
     */
    public function callbackAction(EndorsementRequest $id, Request $request)
    {
        $status = $request->request->get('SmsStatus');

        if (in_array($status, $this->allowedEvents)) {
            $getter = sprintf('get%s', ucfirst($status));
            $setter = sprintf('set%s', ucfirst($status));

            // Record just one stat per endorsement request
            if (null === $id->$getter()) {
                $this->get('twilio_service')->recordStatistic($id, $status);
            }

            $id->$setter(new \DateTime);
            $this->get('endorsement_request_service')->save($id);
        }

        return $this->view([], Response::HTTP_OK);
    }
}
