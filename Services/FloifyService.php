<?php

namespace AppBundle\Services;

use AppBundle\Entity\FloifyApiKey;
use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FloifyService
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $floifyEndPoint */
    private $floifyEndPoint;

    /** @var string $floifyXApiKey */
    private $floifyXApiKey;

    /** @var string $floifyLoanFlowsUri */
    private $floifyLoanFlowsUri = "/flows";

    /** @var string $floifyLoanMetaDataUri */
    private $floifyLoanMetaDataUri = "/loan-metadata";

    /** @var EntityManager $em */
    private $em;

    /** @var \AppBundle\Repository\FloifyApiKeyRepository $floifyRepo */
    private $floifyRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $floifyEndPoint,
        string $floifyXApiKey
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->floifyEndPoint = $floifyEndPoint;
        $this->floifyXApiKey = $floifyXApiKey;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->floifyRepo = $this->em->getRepository(FloifyApiKey::class);
    }

    /**
     * @param FloifyApiKey $floifyApiKey
     * @param User $user
     * @return FloifyApiKey|bool
     */
    public function authorize(FloifyApiKey $floifyApiKey, User $user)
    {
        try {
            $client = new Guzzler;

            $response = $client->get(
                $this->floifyEndPoint . $this->floifyLoanFlowsUri,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->floifyXApiKey,
                        'Authorization' => 'Bearer ' . $floifyApiKey->getApiKey()
                    ]
                ]
            );
            $loans = json_decode($response->getBody());
            if($loans && !isset($loans->error)) {
                return $this->persistApiKey($floifyApiKey, $user);
            }
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param FloifyApiKey $floifyApiKey
     * @param User $user
     * @return FloifyApiKey
     */
    private function persistApiKey(FloifyApiKey $floifyApiKey, User $user)
    {
        $apiKeyExists = $this->floifyRepo->findOneBy(["user" => $user->getId()]);

        /** @var FloifyApiKey $apiKey */
        $apiKey = $apiKeyExists ? $apiKeyExists : new FloifyApiKey;

        $apiKey->setUser($user);
        $apiKey->setApiKey($floifyApiKey->getApiKey());
        $apiKey->setLoanStatus(($apiKeyExists) ? $apiKeyExists->getLoanStatus() : 'Loan Funded!');
        $this->em->persist($apiKey);
        $this->em->flush();

        return $apiKey;
    }

    /**
     * @param FloifyApiKey $floifyApiKey
     * @param String $loan_status
     * @return FloifyApiKey
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setLoanStatus(FloifyApiKey $floifyApiKey, String $loan_status)
    {
        $floifyApiKey->setLoanStatus($loan_status);

        $this->em->persist($floifyApiKey);
        $this->em->flush();

        return $floifyApiKey;
    }

    /**
     * @param FloifyApiKey $floifyApiKey
     * @param User $user
     * @return FloifyApiKey|bool
     */
    public function getLoanPipelines(FloifyApiKey $floifyApiKey)
    {
        try {
            $client = new Guzzler;

            $response = $client->get(
                $this->floifyEndPoint . $this->floifyLoanFlowsUri,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->floifyXApiKey,
                        'Authorization' => 'Bearer ' . $floifyApiKey->getApiKey()
                    ]
                ]
            );
            $loans = json_decode($response->getBody());
            if($loans && !isset($loans->error)) {
                return $loans;
            }
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param int $loanId
     * @param FloifyApiKey $floifyApiKey
     * @return FloifyApiKey|bool
     */
    public function getLoanDetails(int $loanId, FloifyApiKey $floifyApiKey) {
        try {
            $client = new Guzzler;

            $response = $client->get(
                $this->floifyEndPoint . $this->floifyLoanMetaDataUri . "/" . $loanId,
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'X-API-KEY' => $this->floifyXApiKey,
                        'Authorization' => 'Bearer ' . $floifyApiKey->getApiKey()
                    ]
                ]
            );
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }
}
