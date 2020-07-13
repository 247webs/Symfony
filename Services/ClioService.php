<?php

namespace AppBundle\Services;

use AppBundle\Entity\ClioToken;
use AppBundle\Entity\ClioUser;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClioService
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $clioClientId */
    private $clioClientId;

    /** @var string $clioClientSecret */
    private $clioClientSecret;

    /** @var string $clioEndPoint */
    private $clioEndPoint;

    /** @var string $clioAuthEndPoint */
    private $clioAuthEndPoint;

    /** @var string $clioUsersUri */
    private $clioUsersUri = "/api/v4/users";

    /** @var string $clioMattersUri */
    private $clioMattersUri = "/api/v4/matters";

    /** @var EntityManager $em */
    private $em;

    /** @var \AppBundle\Repository\ClioTokenRepository $clioTokenRepo */
    private $clioTokenRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $clioClientId,
        string $clioClientSecret,
        string $clioEndPoint,
        string $clioAuthEndPoint
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->clioClientId = $clioClientId;
        $this->clioClientSecret = $clioClientSecret;
        $this->clioEndPoint = $clioEndPoint;
        $this->clioAuthEndPoint = $clioAuthEndPoint;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->clioTokenRepo = $this->em->getRepository(ClioToken::class);
    }

    /**
     * @param $auth_code
     * @param $redirect_uri
     * @param Company $company
     * @return ClioToken|bool
     */
    public function generateToken(string $auth_code, string $redirect_uri, Company $company)
    {
        $data = [
            "grant_type" => "authorization_code",
            "client_id" => $this->clioClientId,
            "client_secret" => $this->clioClientSecret,
            "redirect_uri" => $redirect_uri,
            "code" => $auth_code
        ];

        try {
            $client = new Guzzler;

            $response = $client->post(
                $this->clioAuthEndPoint,
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $data
                ]
            );

            return $this->persistToken(json_decode($response->getBody()->getContents()), $company, $redirect_uri);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param $data
     * @param Company $company
     * @param string $redirect_uri
     * @return ClioToken
     */
    private function persistToken($data, Company $company, string $redirect_uri)
    {
        $tokenExist = $this->clioTokenRepo->findOneBy(["company" => $company->getId()]);

        /** @var ClioToken $token */
        $token = $tokenExist ? $tokenExist : new ClioToken;

        $token->setCompany($company);
        $token->setAccessToken($data->access_token);
        $token->setTokenType($data->token_type);
        $token->setExpiresIn($data->expires_in);
        $token->setCreatedAt(new \DateTime());
        $token->setRedirectUri($redirect_uri);

        // refreshToken API call does not return new refresh_token
        if (isset($data->refresh_token)) {
            $token->setRefreshToken($data->refresh_token);
        }

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    /**
     * @param ClioToken $token
     * @return ClioToken|bool
     */
    private function getAccessToken(ClioToken $token)
    {
        return $this->isTokenExpired($token) ? $this->refreshToken($token) : $token;
    }

    /**
     * @param ClioToken $token
     * @return bool
     */
    private function isTokenExpired(ClioToken $token)
    {
        $expiringAt = $token->getCreatedAt()->format('U') + $token->getExpiresIn();

        return (time() > $expiringAt);
    }

    /**
     * @param ClioToken $token
     * @return ClioToken|bool
     */
    public function refreshToken(ClioToken $token)
    {
        $data = [
            "grant_type" => "refresh_token",
            "client_id" => $this->clioClientId,
            "client_secret" => $this->clioClientSecret,
            "redirect_uri" => $token->getRedirectUri(),
            "refresh_token" => $token->getRefreshToken()
        ];

        try {
            $client = new Guzzler;

            $response = $client->post(
                $this->clioAuthEndPoint,
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $data
                ]
            );

            return $this->persistToken(
                json_decode($response->getBody()->getContents()),
                $token->getCompany(),
                $token->getRedirectUri()
            );
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param ClioToken $token
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getUsers(ClioToken $token)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->clioEndPoint . $this->clioUsersUri,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param ClioToken $token
     * @param string $since
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getClosedMatters(ClioToken $token, string $since)
    {
        $token = $this->getAccessToken($token);

        if (!$token) {
            return false;
        }

        $data = [
            'fields' => 'id,status,client{id,type,name,first_name,last_name,primary_email_address,' .
                'primary_phone_number},responsible_attorney{id,name,first_name,last_name,email,phone_number,roles}',
            'status' => 'closed',
            'updated_since' => $since
        ];

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->clioEndPoint . $this->clioMattersUri,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token->getAccessToken(),
                    ],
                    'form_params' => $data
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param ClioToken $token
     * @return ClioUser
     */
    public function getMappedUsers(ClioToken $token)
    {
        return $this->em->getRepository(ClioUser::class)
            ->findBy(['clioToken' => $token->getId()]);
    }

    /**
     * @param ClioToken $token
     * @param array $datum
     * @return ClioUser
     */
    public function createUserFromArray(ClioToken $token, array $datum)
    {
        $user = new ClioUser;
        $user->setClioToken($token);
        $user->setUser($this->em->getReference(User::class, $datum["user"]));
        $user->setClioUserId($datum["clio_user"]);

        return $user;
    }

    /**
     * @param ClioToken $token
     * @param array $users
     * @return \stdClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addUsers(ClioToken $token, array $users)
    {
        // Remove all users from token
        $this->truncateUsers($token);

        $results = new \stdClass;
        $results->added = [];
        $results->notAdded = [];

        $accessibleUsers = $this->getUsers($token);

        /** @var ClioUser $user */
        foreach ($users as $user) {
            if ($this->canAccessUser($user, $accessibleUsers['data'])) {
                $this->em->persist($user);
                $results->added[] = $user->getUser()->getFirstName() . ' ' . $user->getUser()->getLastName();
            } else {
                $results->notAdded[] = $user->getUser()->getFirstName() . ' ' . $user->getUser()->getLastName();
            }
        }

        if (count($results->added)) {
            $this->em->flush();
        }

        return $results;
    }

    /**
     * @param ClioToken $token
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function truncateUsers(ClioToken $token)
    {
        $users = $this->em->getRepository(ClioUser::class)
            ->findBy(['clioToken' => $token->getId()]);

        if (count($users)) {
            foreach ($users as $user) {
                $this->em->remove($user);
            }
            $this->em->flush();
        }
    }

    /**
     * @param ClioUser $user
     * @param array $accessibleUsers
     * @return bool
     */
    private function canAccessUser(ClioUser $user, array $accessibleUsers)
    {
        foreach ($accessibleUsers as $accessibleUser) {
            if ($accessibleUser["id"] == $user->getClioUserId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param ClioToken $token
     * @param int $clioUserId
     * @return ClioUser
     */
    public function getMappedUser(ClioToken $token, int $clioUserId)
    {
        return $this->em->getRepository(ClioUser::class)
            ->findOneBy(['clioToken' => $token->getId(), 'clioUserId' => $clioUserId]);
    }
}