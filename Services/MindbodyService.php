<?php

namespace AppBundle\Services;

use AppBundle\Entity\MindbodyToken;
use AppBundle\Entity\MindbodyStaff;
use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MindbodyService
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $mindbodyBaseurl */
    private $mindbodyBaseurl;

    /** @var string $mindbodyTokenUri */
    private $mindbodyTokenUri = '/usertoken/issue';

    /** @var string $mindbodyStaffUri */
    private $mindbodyStaffUri = '/Staff/Staff';

    /** @var string $mindbodyAppointmentUri */
    private $mindbodyAppointmentUri = '/appointment/staffappointments';

    /** @var string $mindbodyClientUri */
    private $mindbodyClientUri = '/client/clients';

    /** @var EntityManager $em */
    private $em;

    /** @var \AppBundle\Repository\MindbodyTokenRepository $mindbodyTokenRepo */
    private $mindbodyTokenRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $mindbodyBaseurl
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->mindbodyBaseurl = $mindbodyBaseurl;

        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->mindbodyTokenRepo = $this->em->getRepository(MindbodyToken::class);
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @param Company $company
     * @return MindbodyToken|bool
     */
    public function generateToken(MindbodyToken $mindbodyToken, Company $company)
    {
        try {
            $client = new Guzzler;

            $response = $client->post(
                $this->mindbodyBaseurl . $this->mindbodyTokenUri,
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded', 'API-Key' => $mindbodyToken->getApiKey(), 'SiteId' => $mindbodyToken->getSiteId()],
                    'form_params' => ['username' => $mindbodyToken->getUsername(),'password' => $mindbodyToken->getPassword()]
                ]
            );
            return $this->persistToken(json_decode($response->getBody()->getContents()), $mindbodyToken, $company);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param $data
     * @param MindbodyToken $mindbodyToken
     * @param Company $company
     * @return MindbodyToken
     */
    public function persistToken($data, MindbodyToken $mindbodyToken, Company $company)
    {
        $tokenExist = $this->mindbodyTokenRepo->findOneBy(["company" => $company->getId()]);

        $token = $tokenExist ? $tokenExist : new MindbodyToken;
        $token->setCompany($company);
        $token->setApiKey($mindbodyToken->getApiKey());
        $token->setSiteId($mindbodyToken->getSiteId());
        $token->setUsername($mindbodyToken->getUsername());
        $token->setPassword($mindbodyToken->getPassword());
        $token->setAccessToken($data->AccessToken);
        $token->setTokenType($data->TokenType);

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getStaffMembers(MindbodyToken $mindbodyToken)
    {
        $mindbodyToken = $this->getAccessToken($mindbodyToken);

        try {
            $client = new Guzzler;

            $limit = 200;

            $response = $client->get(
                $this->mindbodyBaseurl . $this->mindbodyStaffUri.'?limit='.$limit,
                [
                    'headers' => ['API-Key' => $mindbodyToken->getApiKey(), 'SiteId' => $mindbodyToken->getSiteId(), 'Authorization' => $mindbodyToken->getAccessToken()]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return MindbodyToken|bool
     */
    private function getAccessToken(MindbodyToken $mindbodyToken)
    {
        return $this->isTokenExpired($mindbodyToken) ? $this->generateToken($mindbodyToken, $mindbodyToken->getCompany()) : $mindbodyToken;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return bool
     */
    private function isTokenExpired(MindbodyToken $mindbodyToken)
    {
        try {
            $client = new Guzzler;

            $limit = 200;

            $response = $client->get(
                $this->mindbodyBaseurl . $this->mindbodyStaffUri.'?limit='.$limit,
                [
                    'headers' => ['API-Key' => $mindbodyToken->getApiKey(), 'SiteId' => $mindbodyToken->getSiteId(), 'Authorization' => $mindbodyToken->getAccessToken()]
                ]
            );

            return false;
        } catch (RequestException $e) {
            return true;
        }
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return MindbodyStaff
     */
    public function getMappedStaffMembers(MindbodyToken $mindbodyToken)
    {
        return $this->em->getRepository(MindbodyStaff::class)
            ->findBy(['mindbodyToken' => $mindbodyToken->getId()]);
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @param array $datum
     * @return MindbodyStaff
     */
    public function createStaffMemberFromArray(MindbodyToken $mindbodyToken, array $datum)
    {
        $staff = new MindbodyStaff;
        $staff->setMindbodyToken($mindbodyToken);
        $staff->setUser($this->em->getReference(User::class, $datum["user"]));
        $staff->setMindbodyStaffId($datum["staff_member_id"]);

        return $staff;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @param array $staffmembers
     * @return \stdClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function mapStaffMembers(MindbodyToken $mindbodyToken, array $staffmembers)
    {
        // Remove all staff members
        $this->truncateStaffMembers($mindbodyToken);

        $results = new \stdClass;
        $results->added = [];
        $results->notAdded = [];

        $accessibleStaffMembers = $this->getStaffMembers($mindbodyToken);

        /** @var MindbodyStaff $staffmember */
        foreach ($staffmembers as $staffmember) {

            if ($this->canAccessStaffmember($staffmember, $accessibleStaffMembers)) {
                $this->em->persist($staffmember);
                $results->added[] = $staffmember->getUser()->getFirstName() . ' ' . $staffmember->getUser()->getLastName();
            } else {
                $results->notAdded[] = $staffmember->getUser()->getFirstName() . ' ' . $staffmember->getUser()->getLastName();
            }
        }

        if (count($results->added)) {
            $this->em->flush();
        }

        return $results;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function truncateStaffMembers(MindbodyToken $mindbodyToken)
    {
        $staffMembers = $this->em->getRepository(MindbodyStaff::class)
            ->findBy(['mindbodyToken' => $mindbodyToken->getId()]);

        if (count($staffMembers)) {
            foreach ($staffMembers as $staffMember) {
                $this->em->remove($staffMember);
            }

            $this->em->flush();
        }
    }

    /**
     * @param MindbodyStaff $staffmember
     * @param array $accessibleStaffMembers
     * @return bool
     */
    private function canAccessStaffmember(MindbodyStaff $staffmember, array $accessibleStaffMembers)
    {
        if(isset($accessibleStaffMembers['StaffMembers']) && !empty($accessibleStaffMembers['StaffMembers'])) {
            foreach ($accessibleStaffMembers["StaffMembers"] as $accessibleStaffMember) {
                if ($accessibleStaffMember["Id"] == $staffmember->getMindbodyStaffId()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAppointments(MindbodyToken $mindbodyToken)
    {
        $mindbodyToken = $this->getAccessToken($mindbodyToken);

        try {
            $client = new Guzzler;

            $limit      = 200;
            $startDate  = $this->getStartDate();
            $endDate    = $this->getEndDate();;

            $query_params  = 'Limit='.$limit;
            $query_params .= '&StartDate='.$startDate;
            $query_params .= '&EndDate='.$endDate;

            $response = $client->get(
                $this->mindbodyBaseurl . $this->mindbodyAppointmentUri . '?' . $query_params,
                [
                    'headers' => ['API-Key' => $mindbodyToken->getApiKey(), 'SiteId' => $mindbodyToken->getSiteId(), 'Authorization' => $mindbodyToken->getAccessToken()]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @return Datetime
     */
    private function getStartDate() {

        /** Get Three days before date */
        $date = date("Y-m-d", strtotime('-1 days'));

        /** Prepare the start date */
        $startDate = $date . 'T00:00:00';

        return $startDate;
    }

    /**
     * @return Datetime
     */
    private function getEndDate() {

        /** Get Ten days before date */
        $date = date("Y-m-d");

        /** Prepare the end date */
        $endDate = $date . 'T23:59:59';

        return $endDate;
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @param int $staffId
     * @return MindbodyStaff
     */
    public function getOfferStaff(MindbodyToken $mindbodyToken, int $staffId)
    {
        return $this->em->getRepository(MindbodyStaff::class)
            ->findOneBy(['mindbodyToken' => $mindbodyToken->getId(), 'mindbodyStaffId' => $staffId]);
    }

    /**
     * @param MindbodyToken $mindbodyToken
     * @param string $clientId
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClient(MindbodyToken $mindbodyToken, string $clientId)
    {
        $mindbodyToken = $this->getAccessToken($mindbodyToken);

        try {
            $client = new Guzzler;

            $query_params = 'ClientIds='.$clientId;

            $response = $client->get(
                $this->mindbodyBaseurl . $this->mindbodyClientUri .'?'. $query_params,
                [
                    'headers' => ['API-Key' => $mindbodyToken->getApiKey(), 'SiteId' => $mindbodyToken->getSiteId(), 'Authorization' => $mindbodyToken->getAccessToken()]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }
}
