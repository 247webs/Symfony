<?php

namespace AppBundle\Services;

use AppBundle\Entity\EncompassToken;
use AppBundle\Entity\EncompassLoanOfficer;
use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EncompassService
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $encompassEndPoint */
    private $encompassEndPoint;

    /** @var string $encompassAuthEndPoint */
    private $encompassAuthEndPoint;

    /** @var string $encompassTokenIntrospectionUri */
    private $encompassTokenIntrospectionUri = "/introspection";

    /** @var string $encompassUsersListUri */
    private $encompassUsersListUri = "/encompass/v1/company/users";

    /** @var string $encompassLoanPipelineUri */
    private $encompassLoanPipelineUri = "/encompass/v1/loanPipeline";

    /** @var string $encompassLoanDetailsUri */
    private $encompassLoanDetailsUri = "/encompass/v1/loans";

    /** @var EntityManager $em */
    private $em;

    /** @var \AppBundle\Repository\EncompassTokenRepository $encompassRepo */
    private $encompassRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $encompassEndPoint,
        string $encompassAuthEndPoint
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->encompassEndPoint = $encompassEndPoint;
        $this->encompassAuthEndPoint = $encompassAuthEndPoint;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->encompassRepo = $this->em->getRepository(EncompassToken::class);
    }

    /**
     * @param EncompassToken $encompassToken
     * @param Company $company
     * @return EncompassToken|bool
     */
    public function generateToken(EncompassToken $encompassToken, Company $company)
    {
        $data = [
            "grant_type" => "client_credentials",
            "instance_id" => $encompassToken->getInstanceId(),
            "scope" => "lp",
            "client_id" => $encompassToken->getClientId(),
            "client_secret" => $encompassToken->getClientSecret()
        ];

        try {
            $client = new Guzzler;

            $response = $client->post(
                $this->encompassAuthEndPoint,
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $data
                ]
            );

            return $this->persistToken(json_decode($response->getBody()), $encompassToken, $company);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $encompassToken
     * @return bool
     */
    public function isTokenAlreadyExist(EncompassToken $encompassToken) {
        return $this->encompassRepo->findOneBy(["clientId" => $encompassToken->getClientId(), "instanceId" => $encompassToken->getInstanceId()]);
    }

    /**
     * @param $data
     * @param EncompassToken $encompassToken
     * @param Company $company
     * @return EncompassToken
     */
    private function persistToken($data, EncompassToken $encompassToken, Company $company)
    {
        $tokenExists = $this->encompassRepo->findOneBy(["company" => $company->getId()]);

        /** @var EncompassToken $token */
        $token = $tokenExists ? $tokenExists : new EncompassToken;

        $tokenExpire = time() + 120*60;

        $token->setCompany($company);
        $token->setInstanceId($encompassToken->getInstanceId());
        $token->setClientId($encompassToken->getClientId());
        $token->setClientSecret($encompassToken->getClientSecret());
        $token->setAccessToken($data->access_token);
        $token->setTokenType($data->token_type);
        $token->setLoanStatus(($tokenExists) ? $tokenExists->getLoanStatus() : 'Funding');
        $token->setTokenExpire($tokenExpire);
        $token->setLastUsed(time());
        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param EncompassToken $token
     * @param String $loan_status
     * @return EncompassToken
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setLoanStatus(EncompassToken $token, String $loan_status)
    {
        $token->setLoanStatus($loan_status);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param EncompassToken $token
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLoanOfficers(EncompassToken $token)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->encompassEndPoint . $this->encompassUsersListUri,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ],
                    'query' => ['roleId' => 1] //1=LoanOfficers
                ]
            );

            $this->updateTokenLastUsedTime($token);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $token
     * @return EncompassToken
     */
    private function updateTokenLastUsedTime($token)
    {
        $token->setLastUsed(time());

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param EncompassToken $token
     * @return EncompassToken|bool
     */
    private function getAccessToken(EncompassToken $token)
    {
        return $this->isTokenExpired($token) ? $this->generateToken($token, $token->getCompany()) : $token;
    }

    /**
     * @param EncompassToken $token
     * @return bool
     */
    private function isTokenExpired(EncompassToken $token)
    {
        //Check if access token is expired
        if(($token->getTokenExpire() < time()) || ($token->getLastUsed() < (time() - 15*60))){
            return true;
        }
        return false;
    }

    /**
     * @param EncompassToken $token
     * @param int $limit
     * @param string $loanStatusTrigger
     * @return bool|mixed
     */
    public function getLoanPipelines(EncompassToken $token, int $limit, string $loanStatusTrigger)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $data = [
            "filter" => [
                "terms" => [
                    [
                        "canonicalName" => "Fields.Log.MS.Date.".$loanStatusTrigger,
                        "value" => date("m/d/Y", strtotime("-50 day")),
                        "matchType" => "greaterThanOrEquals",
                        "precision" => "day"
                    ]
                ]
            ],
            "fields" => [
                "Loan.LoanFolder",
                "Loan.LoanNumber",
                "Loan.LastModified",
                "Loan.BorrowerFirstName",
                "Loan.BorrowerLastName",
                "Loan.CoBorrowerFirstName",
                "Loan.CoBorrowerLastName",
                "Loan.CurrentCoreMilestoneName",
                "Fields.LoanTeamMember.Name.Loan Officer",
                "Loan.Active",
                "Loan.NextMilestoneDate",
                "NextMilestone.MilestoneName",
                "CurrentLoanAssociate.FullName",
                "Loan.DateCreated",
                "Loan.LastModified",
                "Loan.DateOfEstimatedCompletion",
                "Loan.DateofFinalAction",
                "Loan.DateCompleted",
                "Loan.DateFileOpened",
                "Loan.CurrentMilestoneDate",
                "Loan.LastMilestoneSorted",
                "Loan.CurrentMilestoneName"
            ],
            "sortOrder" => [
                [
                    "canonicalName" => "Loan.DateCreated",
                    "order" => "ASC"
                ]
            ]
        ];

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $this->encompassEndPoint . $this->encompassLoanPipelineUri ."?limit=".$limit,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($data),
              CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $token->getAccessToken(),
                "Cache-Control: no-cache",
                "Content-Type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            $this->updateTokenLastUsedTime($token);

            return $err ? false : json_decode($response, true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $token
     * @param string $loanGuid
     * @param int $roleId
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLoanAssociate(EncompassToken $token, string $loanGuid)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->encompassEndPoint . $this->encompassLoanDetailsUri . '/' . $loanGuid . '/associates',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ],
                    'query' => ['roleId' => 1] // 1 = LoanOfficers
                ]
            );

            $content = json_decode($response->getBody()->getContents(), true);

            $this->updateTokenLastUsedTime($token);

            return (count($content)) ? $content[0] : false;
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $token
     * @param string $loanGuid
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLoanBorrowersAndCoborrowers(EncompassToken $token, string $loanGuid)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->encompassEndPoint . $this->encompassLoanDetailsUri . '/' .
                    $loanGuid . '/?entities=Borrower,CoBorrower',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ],
                    'query' => ['roleId' => 1] // 1=LoanOfficers
                ]
            );

            $this->updateTokenLastUsedTime($token);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $token
     * @param string $loanGuid
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLoanMilestones(EncompassToken $token, string $loanGuid)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->encompassEndPoint . $this->encompassLoanDetailsUri . '/' . $loanGuid . '/milestones',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ]
                ]
            );

            $this->updateTokenLastUsedTime($token);

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param EncompassToken $encompassToken
     * @param array $loanOfficer
     * @return EncompassLoanOfficer
     */
    public function getOfferLoanOfficer(EncompassToken $encompassToken, array $loanOfficer)
    {
        $loanOfficerId = $loanOfficer['id'];
        return $this->em->getRepository(EncompassLoanOfficer::class)
            ->findOneBy(['encompassToken' => $encompassToken->getId(), 'loanOfficerId' => $loanOfficerId]);
    }

    /**
     * @param EncompassToken $encompassToken
     * @param array $datum
     * @return EncompassLoanOfficer
     */
    public function createLoanOfficerFromArray(EncompassToken $encompassToken, array $datum)
    {
        $loanOfficer = new EncompassLoanOfficer;
        $loanOfficer->setEncompassToken($encompassToken);
        $loanOfficer->setUser($this->em->getReference(User::class, $datum["user"]));
        $loanOfficer->setLoanOfficerId($datum["loan_officer"]);

        return $loanOfficer;
    }

    /**
     * @param EncompassToken $encompassToken
     * @param array $loanOfficers
     * @return \stdClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addLoanOfficers(EncompassToken $encompassToken, array $loanOfficers)
    {
        // Remove all loanOfficers from encompassToken
        $this->truncateLoanOfficers($encompassToken);

        $results = new \stdClass;
        $results->added = [];
        $results->notAdded = [];

        $accessibleLoanOfficers = $this->getLoanOfficers($encompassToken);

        /** @var EncompassLoanOfficer $loanOfficer */
        foreach ($loanOfficers as $loanOfficer) {
            if ($this->canAccessProvider($loanOfficer, $accessibleLoanOfficers)) {
                $this->em->persist($loanOfficer);
                $results->added[] = $loanOfficer->getUser()->getFirstName() .
                    ' ' . $loanOfficer->getUser()->getLastName();
            } else {
                $results->notAdded[] = $loanOfficer->getUser()->getFirstName() .
                    ' ' . $loanOfficer->getUser()->getLastName();
            }
        }

        if (count($results->added)) {
            $this->em->flush();
        }

        return $results;
    }

    /**
     * @param EncompassToken $encompassToken
     * @return EncompassLoanOfficer
     */
    public function getMappedLoanOfficers(EncompassToken $encompassToken)
    {
        return $this->em->getRepository(EncompassLoanOfficer::class)
            ->findBy(['encompassToken' => $encompassToken->getId()]);
    }


    /**
     * @param EncompassToken $encompassToken
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function truncateLoanOfficers(EncompassToken $encompassToken)
    {
        $loanOfficers = $this->em->getRepository(EncompassLoanOfficer::class)
            ->findBy(['encompassToken' => $encompassToken->getId()]);

        if (count($loanOfficers)) {
            foreach ($loanOfficers as $loanOfficer) {
                $this->em->remove($loanOfficer);
            }

            $this->em->flush();
        }
    }

    /**
     * @param EncompassLoanOfficer $loanOfficer
     * @param array $accessibleLoanOfficers
     * @return bool
     */
    private function canAccessProvider(EncompassLoanOfficer $loanOfficer, array $accessibleLoanOfficers)
    {
        foreach ($accessibleLoanOfficers as $accessibleLoanOfficer) {
            if ($accessibleLoanOfficer["id"] == $loanOfficer->getLoanOfficerId()) {
                return true;
            }
        }

        return false;
    }
}
