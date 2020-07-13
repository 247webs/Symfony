<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Contact;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Route("/zapier")
 */
class ZapierController extends FOSRestController
{
    /**
     * @Rest\Post(path="/contact", name="zapier_contact_post")
     *
     * @ParamConverter(
     *     "contact",
     *     converter="fos_rest.request_body",
     *     options={
     *         "validator"={
     *              "groups"="contact_post"
     *          }
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Contact",
     *      description="Create a contact",
     *      https="true",
     *      statusCodes={
     *          201="Success",
     *          400="Invalid data"
     *      }
     * )
     */
    public function postAction(Contact $contact, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            $response = [];

            $message = '';

            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $key => $violation) {
                $message .= ($key == 0) ? $violation->getMessage() : ' ' . $violation->getMessage();
            }

            $response[] = $message;

            return $this->view($response, Response::HTTP_BAD_REQUEST);
        }

        return $this->view($this->get('contact_service')->create($contact, $this->getUser()), Response::HTTP_CREATED);
    }
}
