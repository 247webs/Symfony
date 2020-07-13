<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Branch;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Model\ApiProblem;
use AppBundle\Representation\PublicEndorsements;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation as Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @Route("/branch") */
class BranchController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="branch_get", requirements={"id": "\d+"})
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Get branch",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(Branch $id)
    {
        if ($this->getUser()->getBranch()->getId() !== $id->getId() &&
            !$this->get('user_service')->getIsAdmin($this->getUser())
        ) {
            throw new AccessDeniedHttpException("You are not permitted to access this resource");
        }

        return $id;
    }

    /**
     * @Rest\Get(path="/{branch}/endorsements", name="branch_endorsements_get")
     *
     * @ParamConverter(
     *     "branch",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Rest\QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      default="500",
     *      description="Max number of results"
     * )
     *
     * @Rest\QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      default="1",
     *      description="The page"
     * )
     *
     * @Rest\QueryParam(
     *      name="videos_only",
     *      requirements="true|false",
     *      default="false",
     *      description="Only return endorsements with videos"
     * )
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Get branch endorsements",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Branch not found"
     *     }
     * )
     */
    public function getEndorsementsAction(Branch $branch, ParamFetcherInterface $paramFetcher)
    {
        ini_set('memory_limit', '1G'); // Boost the memory
        ini_set('max_execution_time', 0); // Allow unlimited execution time for companies only

        $page = $paramFetcher->get('page');
        if (empty($page)) {
            $page = 1;
        }

        $limit = $paramFetcher->get('limit');
        if (empty($limit)) {
            $limit = 500;
        }

        $videosOnly = !empty($paramFetcher->get('videos_only')) &&
        'true' == strtolower($paramFetcher->get('videos_only'))
            ? true : false;

        $endorsements = $this->get('branch_service')->getBranchEndorsementFeed($branch, $videosOnly);

        if (!count($endorsements)) {
            throw new NotFoundHttpException($branch->getName() . " does not have any endorsements to share yet");
        }

        $statService = $this->get('statistic_service');

        return new PublicEndorsements(
            $statService->calculateAverageScoreByBranch($branch),
            $endorsements,
            $statService->countScorableEndorsementsByBranch($branch),
            $page,
            $limit
        );
    }

    /**
     * @Rest\Get(path="/{branch}/endorsements/summary", name="branch_endorsements_summary_get")
     *
     * @ParamConverter(
     *     "branch",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Get branch endorsements summary",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Branch not found"
     *     }
     * )
     */
    public function getEndorsementsSummaryAction(Branch $branch)
    {
        $response = new \StdClass;
        $statService = $this->get('statistic_service');


        $response->total_endorsements = $statService->countScorableEndorsementsByBranch($branch);
        $response->average_rating = $statService->calculateAverageScoreByBranch($branch);

        return $this->view((array) $response, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(path="/{id}", name="branch_put")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "branch",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Modify a branch",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data",
     *         404="Branch not found"
     *     }
     * )
     */
    public function putAction(
        Branch $id,
        Branch $branch,
        ConstraintViolationListInterface $violations
    ) {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        if ($this->getUser()->getBranch()->getId() !== $id->getId() &&
            !$this->get('security.authorization_checker')->isGranted('ROLE_COMPANY_ADMIN') &&
            !$this->get('user_service')->getIsAdmin($this->getUser())
        ) {
            throw new AccessDeniedHttpException("You are not permitted to access this resource");
        }

        return $this->view($this->get('branch_service')->updateBranch($id, $branch), Response::HTTP_OK);
    }

    /**
     * @Rest\Post(path="", name="branch_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "branch",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="branch_post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Create a branch",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(Branch $branch, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        return $this->view($this->get('branch_service')->createBranch($branch), Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete(path="/{id}", name="branch_delete")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Branch",
     *      description="Delete a branch",
     *      https="true",
     *      statusCodes={
     *         204="Branch deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function deleteAction(Branch $id)
    {
        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }
}
