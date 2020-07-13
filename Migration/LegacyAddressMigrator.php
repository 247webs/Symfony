<?php

namespace AppBundle\Migration;

use AppBundle\Entity\ProfileAddress;
use Doctrine\ORM\EntityManager;
use PDO;

class LegacyAddressMigrator
{
    private $em;
    private $dataMigrator;
    private $dbh;
    private $userRepo;

    public function __construct(
        EntityManager $em,
        DataMigrator $dataMigrator,
        $databaseHost,
        $importDbName,
        $importDbUser,
        $importDbPass
    ) {
        $this->dbh = new PDO('mysql:host=' . $databaseHost . ';dbname=' . $importDbName, $importDbUser, $importDbPass);
        $this->em = $em;
        $this->dataMigrator = $dataMigrator;
        $this->userRepo = $this->em->getRepository('AppBundle:UserProfile');
    }

    public function migrate(int $userId)
    {
        $sql = "SELECT u.*, up.company_name, up.address1, up.address2, up.city, up.credentials as cred, " .
            "st.name as state, co.name as country, up.postal_code as zip_code, up.blurb, up.web_site as website, " .
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
            if (null !== $legacyUser['address1'] &&
                null !== $legacyUser['city'] &&
                null !== $legacyUser['state'] &&
                null !== $legacyUser['zip_code']
            ) {
                $profile = $this->userRepo->findOneBy(['user' => $legacyUser['id']]);

                if ($profile) {
                    printf(
                        "Migrating %s's address \n",
                        $profile->getUser()->getFirstName() . ' ' . $profile->getUser()->getLastName()
                    );

                    $address = new ProfileAddress;
                    $address->setUserProfile($profile);
                    $address->setAddressLine1(trim($legacyUser['address1']));
                    if (null != $legacyUser['address2']) {
                        $address->setAddressLine2(trim($legacyUser['address2']));
                    }
                    $address->setCity(trim($legacyUser['city']));
                    $address->setState($this->dataMigrator->getState(trim($legacyUser['state'])));
                    $address->setZip(trim($legacyUser['zip_code']));

                    $this->em->persist($address);
                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
    }
}
