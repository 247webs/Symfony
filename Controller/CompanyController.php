<?php

namespace AppBundle\Controller;

use AppBundle\Model\ApiProblem;
use AppBundle\Entity\Company;
use AppBundle\Exception\ApiProblemException;
use AppBundle\Report\StatisticsReporter;
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

/** @Route("/company") */
class CompanyController extends FOSRestController
{
    /**
     * @Rest\Get(path="/{id}", name="company_get", requirements={"id": "\d+"})
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Get company",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         401="Unauthorized",
     *         404="Resource not found"
     *     }
     * )
     */
    public function getAction(Company $id)
    {
        if ($this->getUser()->getBranch()->getCompany()->getId() !== $id->getId() &&
            !$this->get('user_service')->getIsAdmin($this->getUser())
        ) {
            throw new AccessDeniedHttpException("You are not permitted to access this resource");
        }

        return $id;
    }

    /**
     * @Rest\Get(path="/{company}/endorsements", name="company_endorsements_get")
     *
     * @ParamConverter(
     *     "company",
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
     *      section="Company",
     *      description="Get company endorsements",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Company not found"
     *     }
     * )
     */
    public function getEndorsementsAction(Company $company, ParamFetcherInterface $paramFetcher)
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

        $endorsements = $this->get('company_service')->getCompanyEndorsementFeed($company, $videosOnly);

        if (!count($endorsements)) {
            throw new NotFoundHttpException($company->getName() . " does not have any endorsements to share yet");
        }

        $statService = $this->get('statistic_service');

        return new PublicEndorsements(
            $statService->calculateAverageScoreByCompany($company),
            $endorsements,
            $statService->countScorableEndorsementsByCompany($company),
            $page,
            $limit
        );
    }

    /**
     * @Rest\Get(path="/{company}/endorsements/summary", name="company_endorsements_summary_get")
     *
     * @ParamConverter(
     *     "company",
     *     options={
     *          "repository_method" = "findOneByIdOrSlug"
     *     }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Get company endorsements summary",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         404="Company not found"
     *     }
     * )
     */
    public function getEndorsementsSummaryAction(Company $company)
    {
        $response = new \StdClass;
        $statService = $this->get('statistic_service');


        $response->total_endorsements = $statService->countScorableEndorsementsByCompany($company);
        $response->average_rating = $statService->calculateAverageScoreByCompany($company);

        return $this->view((array) $response, Response::HTTP_OK);
    }

    /**
     * @Rest\Put(path="/{id}", name="company_put")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "company",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="company_put"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Modify a company",
     *      https="true",
     *      statusCodes={
     *         200="Success",
     *         400="Invalid data",
     *         404="Company not found"
     *     }
     * )
     */
    public function putAction(
        Company $id,
        Company $company,
        ConstraintViolationListInterface $violations
    ) {
        if ($this->getUser()->getBranch()->getCompany()->getId() !== $id->getId() &&
            !$this->get('user_service')->getIsAdmin($this->getUser())
        ) {
            throw new AccessDeniedHttpException("You are not permitted to access this resource");
        }

        if (count($violations)) {
            /** @noinspection PhpUndefinedClassInspection */
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $c = $this->get('company_service')->updateCompany($id, $company);

        return $this->view($c, Response::HTTP_OK);
    }

    /**
     * @Rest\Post(path="", name="company_post")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @ParamConverter(
     *      "company",
     *      converter="fos_rest.request_body",
     *      options={
     *          "validator"={
     *              "groups"="post"
     *          }
     *      }
     * )
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Create a company",
     *      https="true",
     *      statusCodes={
     *         201="Success",
     *         400="Invalid data"
     *     }
     * )
     */
    public function postAction(Company $company, ConstraintViolationListInterface $violations)
    {
        if (count($violations)) {
            /** @noinspection PhpUndefinedClassInspection */
            throw new ApiProblemException(
                new ApiProblem(Response::HTTP_BAD_REQUEST, $violations, ApiProblem::TYPE_VALIDATION_ERROR)
            );
        }

        $c = $this->get('company_service')->createCompany($company);

        return $this->view($c, Response::HTTP_CREATED);
    }

    /**
     * @Rest\Delete(path="/{id}", name="company_delete")
     *
     * @Rest\View(serializerGroups={"private"})
     *
     * @Doc\ApiDoc(
     *      section="Company",
     *      description="Delete a company",
     *      https="true",
     *      statusCodes={
     *         204="Company deleted",
     *         404="Not found"
     *     }
     * )
     */
    public function deleteAction(Company $id)
    {
        $em = $this->getDoctrine()->getManager();
        $id->setActive(false);
        $em->persist($id);
        $em->flush();

        return $this->view([], Response::HTTP_NO_CONTENT);
    }
}
