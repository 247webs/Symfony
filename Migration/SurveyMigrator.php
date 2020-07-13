<?php

namespace AppBundle\Migration;

use Doctrine\ORM\EntityManager;
use PDO;

class SurveyMigrator
{
    /** @var EntityManager $em */
    private $em;

    /**
     * SurveyMigrator constructor.
     * @param EntityManager $em
     * @param $databaseHost
     * @param $importDbName
     * @param $importDbUser
     * @param $importDbPass
     */
    public function __construct(
        EntityManager $em,
        $databaseHost,
        $importDbName,
        $importDbUser,
        $importDbPass
    ) {
        $this->dbh = new PDO('mysql:host=' . $databaseHost . ';dbname=' . $importDbName, $importDbUser, $importDbPass);
        $this->em = $em;
    }

    public function generateSurveyRedirects(array $candidate)
    {
        // Get the users, subusers from the legacy database.
        $sql = "SELECT u.* FROM user u WHERE (u.id = :userId OR u.owner_id = :userId OR u.owner_id IN " .
            "(SELECT id FROM user WHERE owner_id = :userId)) AND u.status = 1 ORDER BY u.id ASC, u.id = :userId";

        $sth = $this->dbh->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
        $sth->execute([
            ':userId' => $candidate['userId'],
        ]);

        $users = $sth->fetchAll(PDO::FETCH_ASSOC);

        printf("Generating redirects for %d accounts", count($users));

        // Generate redirect text, add to array
        $redirects = [];

        foreach ($users as $user) {
            $survey = $this->em->getRepository('AppBundle:Survey')->findOneBy(['user' => $user['id']]);

            if ($survey) {
                $redirects[] = 'Redirect 301 /request/' . $user['username'] . '/service' .
                    ' https://eendorsements.com/survey/' . base64_encode($survey->getId());
            }
        }

        // Return array
        return $redirects;
    }
}
