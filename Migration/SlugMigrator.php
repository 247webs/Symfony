<?php

namespace AppBundle\Migration;

use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;

class SlugMigrator
{
    /** @var EntityManager */
    private $em;

    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        EntityManager $em,
        UserRepository $userRepo
    ) {
        $this->em = $em;
        $this->userRepo = $userRepo;
    }

    public function migrateSlug($candidate)
    {
        $user = $this->userRepo->find($candidate);

        if ($user) {
            $newSlug = strtolower($user->getFirstName()) . '-' . strtolower($user->getLastName());
            $newSlug = str_replace(['&', ' ', '\'', '"', '.', ',', '(', ')'], ['and', '-',''], $newSlug);

            $slugExists = $this->userRepo->findOneBy(['slug' => $newSlug]);

            if ($slugExists) {
                print("Slug " . $newSlug . " already exists \n");
                $newSlug = $newSlug . '-duplicate-' . md5(uniqid());
            }

            $redirect = 'Redirect 301 /user/' . $user->getSlug() . ' https://eendorsements.com/user/' . $newSlug;

            $user->setSlug($newSlug);
            $this->em->persist($user);
            $this->em->flush();
            $this->em->clear();

            return $redirect;
        }

        return false;
    }
}
