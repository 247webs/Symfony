<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Entity\Company;
use AppBundle\Entity\UserProfile;
use AppBundle\Entity\CompanyProfile;
use AppBundle\Model\AgileContact;
use AppBundle\Services\AgileCrmService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;

class AgileCRMSubscriber implements EventSubscriber
{
    /** @var EntityManager $em */
    private $em;

    /** @var AgileCrmService */
    private $agileCrmService;

    /** @var boolean $agileSyncEnabled */
    private $agileSyncEnabled;

    /**
     * AgileCRMListener constructor.
     * @param EntityManager $em
     * @param AgileCrmService $agileCrmService
     */
    public function __construct(EntityManager $em, AgileCrmService $agileCrmService, bool $agileSyncEnabled)
    {
        $this->em = $em;
        $this->agileCrmService = $agileCrmService;
        $this->agileSyncEnabled = $agileSyncEnabled;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if ($this->agileSyncEnabled) {
            if ($args->getObject() instanceof User) {
                /** @var User $user */
                $user = $args->getObject();

                $contact = AgileContact::createFromUser($user);

                $this->agileCrmService->postContact($contact);
            } elseif ($args->getObject() instanceof UserProfile) {
                /** @var UserProfile $userProfile */
                $userProfile = $args->getObject();

                $user = $userProfile->getUser();

                $contact = AgileContact::createFromUser($user);

                $this->agileCrmService->postContact($contact);
            } elseif ($args->getObject() instanceof Company) {
                /** @var Company $company */
                $company = $args->getObject();

                $userRepo = $this->em->getRepository(User::class);

                $users = $userRepo->getUsersByCompany($company);

                foreach ($users as $user) {
                    $contact = AgileContact::createFromUser($user);

                    $this->agileCrmService->postContact($contact);
                }
            } elseif ($args->getObject() instanceof CompanyProfile) {
                /** @var CompanyProfile $companyProfile */
                $companyProfile = $args->getObject();

                $company = $companyProfile->getCompany();

                $userRepo = $this->em->getRepository(User::class);

                $users = $userRepo->getUsersByCompany($company);

                foreach ($users as $user) {
                    $contact = AgileContact::createFromUser($user);

                    $this->agileCrmService->postContact($contact);
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        if ($this->agileSyncEnabled) {
            if ($args->getObject() instanceof User) {
                /** @var User $user */
                $user = $args->getObject();

                $contact = AgileContact::createFromUser($user);

                $this->agileCrmService->putContact($contact);
            } elseif ($args->getObject() instanceof UserProfile) {
                /** @var UserProfile $userProfile */
                $userProfile = $args->getObject();

                $user = $userProfile->getUser();

                $contact = AgileContact::createFromUser($user);

                $this->agileCrmService->putContact($contact);
            } elseif ($args->getObject() instanceof Company) {
                /** @var Company $company */
                $company = $args->getObject();

                $userRepo = $this->em->getRepository(User::class);

                $users = $userRepo->getUsersByCompany($company);

                foreach ($users as $user) {
                    $contact = AgileContact::createFromUser($user);

                    $this->agileCrmService->putContact($contact);
                }
            } elseif ($args->getObject() instanceof CompanyProfile) {
                /** @var CompanyProfile $companyProfile */
                $companyProfile = $args->getObject();

                $company = $companyProfile->getCompany();

                $userRepo = $this->em->getRepository(User::class);

                $users = $userRepo->getUsersByCompany($company);

                foreach ($users as $user) {
                    $contact = AgileContact::createFromUser($user);

                    $this->agileCrmService->putContact($contact);
                }
            }
        }
    }
}
