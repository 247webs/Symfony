<?php

namespace AppBundle\Services;

use AppBundle\Entity\DrchronoPractice;
use AppBundle\Entity\DrchronoDoctor;
use AppBundle\Entity\Company;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DrchronoService
{
    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var ContainerInterface $container */
    private $container;

    /** @var string $drchronoClientId */
    private $drchronoClientId;

    /** @var string $drchronoClientSecret */
    private $drchronoClientSecret;

    /** @var string $drchronoEndPoint */
    private $drchronoEndPoint;

    /** @var string $drchronoAuthEndPoint */
    private $drchronoAuthEndPoint;

    /** @var string $drchronoDoctorsUri */
    private $drchronoDoctorsUri = "/api/doctors";

    /** @var string $drchronoPatientsUri */
    private $drchronoPatientsUri = "/api/patients";

    /** @var string $drchronoAppointmentsUri */
    private $drchronoAppointmentsUri = "/api/appointments";

    /** @var EntityManager $em */
    private $em;

    /** @var \AppBundle\Repository\DrchronoPracticeRepository $drchronoPracticeRepo */
    private $drchronoPracticeRepo;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        ContainerInterface $container,
        string $drchronoClientId,
        string $drchronoClientSecret,
        string $drchronoEndPoint,
        string $drchronoAuthEndPoint
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->container = $container;
        $this->drchronoClientId = $drchronoClientId;
        $this->drchronoClientSecret = $drchronoClientSecret;
        $this->drchronoEndPoint = $drchronoEndPoint;
        $this->drchronoAuthEndPoint = $drchronoAuthEndPoint;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->drchronoPracticeRepo = $this->em->getRepository(DrchronoPractice::class);
    }

    /**
     * @param String $auth_code
     * @param String $redirect_uri
     * @param Company $company
     * @return DrchronoPractice|bool
     */
    public function generateToken(String $auth_code, String $redirect_uri, Company $company)
    {
        $data = [
            "grant_type" => "authorization_code",
            "client_id" => $this->drchronoClientId,
            "client_secret" => $this->drchronoClientSecret,
            "redirect_uri" => $redirect_uri,
            "code" => $auth_code
        ];

        $client = new Guzzler;

        try {
            $response = $client->request(
                'POST',
                $this->drchronoAuthEndPoint,
                [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'form_params' => $data
                ]
            );
            return $this->persistToken(json_decode($response->getBody()), $company, $redirect_uri);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param $data
     * @param Company $company
     * @param String $redirect_uri
     * @return DrchronoPractice
     */
    private function persistToken($data, Company $company, String $redirect_uri)
    {
        $practiceExist = $this->drchronoPracticeRepo->findOneBy(["company" => $company->getId()]);

        /** @var DrchronoPractice $practice */
        $practice = $practiceExist ? $practiceExist : new DrchronoPractice;

        $practice->setCompany($company);
        $practice->setAccessToken($data->access_token);
        $practice->setTokenType($data->token_type);
        $practice->setRefreshToken($data->refresh_token);
        $practice->setRedirectUri($redirect_uri);
        $practice->setExpiresIn($data->expires_in);
        $practice->setCreatedAt(new \DateTime());
        $practice->setAppointmentStatus(($practiceExist) ? $practiceExist->getAppointmentStatus() : 'Complete');
        $this->em->persist($practice);
        $this->em->flush();

        return $practice;
    }

    /**
     * @param DrchronoPractice $practice
     * @param String $appointment_status
     * @return DrchronoPractice
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setAppointmentStatus(DrchronoPractice $practice, String $appointment_status)
    {
        $practice->setAppointmentStatus($appointment_status);

        $this->em->persist($practice);
        $this->em->flush();

        return $practice;
    }

    /**
     * @param DrchronoPractice $practice
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDoctors(DrchronoPractice $practice)
    {
        $practice = $this->getAccessToken($practice);

        if (!$practice) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->drchronoEndPoint . $this->drchronoDoctorsUri,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $practice->getAccessToken(),
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param DrchronoPractice $practice
     * @return DrchronoPractice|bool
     */
    private function getAccessToken(DrchronoPractice $practice)
    {
        return $this->isTokenExpired($practice) ? $this->refreshToken($practice) : $practice;
    }

    /**
     * @param DrchronoPractice $practice
     * @return bool
     */
    private function isTokenExpired(DrchronoPractice $practice)
    {
        $expiringAt = $practice->getCreatedAt()->format('U') + $practice->getExpiresIn();

        return time() > $expiringAt;
    }

    /**
     * @param DrchronoPractice $practice
     * @return DrchronoPractice|bool
     */
    public function refreshToken(DrchronoPractice $practice)
    {
        $data = [
            "grant_type" => "refresh_token",
            "client_id" => $this->drchronoClientId,
            "client_secret" => $this->drchronoClientSecret,
            "redirect_uri" => $practice->getRedirectUri(),
            "refresh_token" => $practice->getRefreshToken()
        ];

        try {
            $client = new Guzzler;

            $response = $client->post(
                $this->drchronoAuthEndPoint,
                [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => $data
                ]
            );

            return $this->persistToken(
                json_decode($response->getBody()),
                $practice->getCompany(),
                $practice->getRedirectUri()
            );
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param DrchronoPractice $practice
     * @return DrchronoDoctor
     */
    public function getMappedDoctors(DrchronoPractice $practice)
    {
        return $this->em->getRepository(DrchronoDoctor::class)
            ->findBy(['drchronoPractice' => $practice->getId()]);
    }

    /**
     * @param DrchronoPractice $practice
     * @param array $datum
     * @return DrchronoDoctor
     */
    public function createDoctorFromArray(DrchronoPractice $practice, array $datum)
    {
        $doctor = new DrchronoDoctor;
        $doctor->setDrchronoPractice($practice);
        $doctor->setUser($this->em->getReference(User::class, $datum["user"]));
        $doctor->setDrchronoDoctorId($datum["doctor"]);

        return $doctor;
    }

    /**
     * @param DrchronoPractice $practice
     * @param array $doctors
     * @return \stdClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addDoctors(DrchronoPractice $practice, array $doctors)
    {
        // Remove all doctors from practice
        $this->truncateDoctors($practice);

        $results = new \stdClass;
        $results->added = [];
        $results->notAdded = [];

        $accessibleDoctors = $this->getDoctors($practice);

        /** @var DrchronoDoctor $doctor */
        foreach ($doctors as $doctor) {
            if ($this->canAccessDoctor($doctor, $accessibleDoctors['results'])) {
                $this->em->persist($doctor);
                $results->added[] = $doctor->getUser()->getFirstName() . ' ' . $doctor->getUser()->getLastName();
            } else {
                $results->notAdded[] = $doctor->getUser()->getFirstName() . ' ' . $doctor->getUser()->getLastName();
            }
        }

        if (count($results->added)) {
            $this->em->flush();
        }

        return $results;
    }

    /**
     * @param DrchronoPractice $practice
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function truncateDoctors(DrchronoPractice $practice)
    {
        $doctors = $this->em->getRepository(DrchronoDoctor::class)
            ->findBy(['drchronoPractice' => $practice->getId()]);

        if (count($doctors)) {
            foreach ($doctors as $doctor) {
                $this->em->remove($doctor);
            }

            $this->em->flush();
        }
    }

    /**
     * @param DrchronoDoctor $doctor
     * @param array $accessibleDoctors
     * @return bool
     */
    private function canAccessDoctor(DrchronoDoctor $doctor, array $accessibleDoctors)
    {
        foreach ($accessibleDoctors as $accessibleDoctor) {
            if ($accessibleDoctor["id"] == $doctor->getDrchronoDoctorId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param DrchronoPractice $practice
     * @param String $since
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAppointments(DrchronoPractice $practice, String $since)
    {
        $practice = $this->getAccessToken($practice);
        if (!$practice) {
            return false;
        }

        $data = ['since' => $since, 'status' => $practice->getAppointmentStatus()];

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->drchronoEndPoint . $this->drchronoAppointmentsUri,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $practice->getAccessToken(),
                    ],
                    'query' => $data
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param DrchronoPractice $practice
     * @param Int $doctorId
     * @return DrchronoDoctor
     */
    public function getEndorsementDoctor(DrchronoPractice $practice, Int $doctorId)
    {
        return $this->em->getRepository(DrchronoDoctor::class)
            ->findOneBy(['drchronoPractice' => $practice->getId(), 'drchronoDoctorId' => $doctorId]);
    }

    /**
     * @param DrchronoPractice $practice
     * @param Int $patientId
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPatient(DrchronoPractice $practice, Int $patientId)
    {
        $practice = $this->getAccessToken($practice);
        if (!$practice) {
            return false;
        }

        $client = new Guzzler;

        try {
            $response = $client->request(
                'GET',
                $this->drchronoEndPoint . $this->drchronoPatientsUri . "/" . $patientId,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $practice->getAccessToken(),
                    ]
                ]
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return false;
        }
    }
}
