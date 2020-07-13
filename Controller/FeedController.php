<?php

namespace AppBundle\Controller;

use AppBundle\Entity\FeedSetting;
use AppBundle\Model\ApiProblem;
use AppBundle\Exception\ApiProblemException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/feed") */
class FeedController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{type}/{slug}", name="feed_get")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @Doc\ApiDoc(
     *      section="Feed",
     *      description="Get feed",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction($type, $slug)
    {
        return $this->get('feed_service')->getFeedSettings($type, $slug);
    }

    /**
     * @Rest\Post(path="", name="feed_post")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @ParamConverter(
     *      "feed",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="feed_setting_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Feed",
     *      description="Create a feed",
     *      https="true",
     *      statusCodes={
     *         201="Feed Created",
     *         400="Invalid data"
     *     }
     * )
     *
     */
    public function postAction(FeedSetting $feed, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $feed);

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view(
            $this->get('feed_service')->setFeed($feed),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Rest\Put(path="/{id}", name="feed_put")
     *
     * @Rest\View(serializerGroups={"public"})
     *
     * @ParamConverter(
     *      "feed",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="feed_setting_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Feed",
     *      description="Update a feed",
     *      https="true",
     *      statusCodes={
     *         200="Feed Updated",
     *         400="Invalid data"
     *     }
     * )
     */
    public function putAction(FeedSetting $id, FeedSetting $feed, ConstraintViolationListInterface $violations)
    {
        $this->denyAccessUnlessGranted('edit', $feed);

        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view(
            $this->get('feed_service')->setFeed($feed),
            Response::HTTP_OK
        );
    }
}
