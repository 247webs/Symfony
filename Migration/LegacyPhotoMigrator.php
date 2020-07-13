<?php

namespace AppBundle\Migration;

use Doctrine\ORM\EntityManager;
use PDO;

class LegacyPhotoMigrator
{
    private $dbh;
    private $em;
    private $userRepo;

    public function __construct(
        EntityManager $em,
        $databaseHost,
        $importDbName,
        $importDbUser,
        $importDbPass
    ) {
        $this->dbh = new PDO('mysql:host=' . $databaseHost . ';dbname=' . $importDbName, $importDbUser, $importDbPass);
        $this->em = $em;
        $this->userRepo = $this->em->getRepository('AppBundle:UserProfile');
    }

    public function migratePhoto(int $userId)
    {
        $sql = "SELECT u.*, up.company_name, up.address1, up.address2, up.city, st.name as state, " .
            "co.name as country, up.postal_code as zip_code, up.blurb, up.web_site as website, " .
            "up.phone_number as phone, cc.token, uf.logo_file, uf.photo_file FROM user u LEFT JOIN " .
            "credit_card cc ON u.credit_card_id = cc.id LEFT JOIN user_profile up ON u.profile_id = up.id " .
            "LEFT JOIN state st ON up.state_id = st.id LEFT JOIN country co ON up.country_id = co.id LEFT JOIN " .
            "user_file uf ON u.file_id = uf.id WHERE (u.id = :userId OR u.owner_id = :userId OR u.owner_id IN " .
            "(SELECT id FROM user WHERE owner_id = :userId)) AND u.status = 1 ORDER BY u.id ASC, u.id = :userId";
        $sth = $this->dbh->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $sth->execute([
            'userId' => $userId,
        ]);

        $legacyUsers = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($legacyUsers as $legacyUser) {
            if (null !== $legacyUser['photo_file']) {
                $profile = $this->userRepo->findOneBy(['user' => $legacyUser['id']]);

                if ($profile) {
                    printf(
                        "Migrating %s's profile to image %s\n",
                        $profile->getUser()->getFirstName() . ' ' . $profile->getUser()->getLastName(),
                        $legacyUser['photo_file']
                    );

                    $profile->setLogo($legacyUser['photo_file']);
                    $this->em->persist($profile);
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
    }
}
