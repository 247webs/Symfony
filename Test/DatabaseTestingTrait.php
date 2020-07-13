<?php

namespace AppBundle\Test;

use AppBundle\Entity\User;
use AppBundle\Services\UserService;
use AppBundle\Utilities\EntityFactory;

trait DatabaseTestingTrait
{
    /**
     * @param array $args
     * @return User
     */
    public function createTestUser($args = [])
    {
        /** @var UserService $userService */
        /** @noinspection PhpUndefinedMethodInspection */
        $userService = $this->getContainer()->get('user_service');
        $user = EntityFactory::aUser($args);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $userService->createUser($user, $user->getPassword(), false);
    }

    /**
     * @param array $args
     * @return User
     */
    public function createTestBranchUser($args = [])
    {
        /** @var UserService $userService */
        /** @noinspection PhpUndefinedMethodInspection */
        $userService = $this->getContainer()->get('user_service');
        $user = EntityFactory::aUserFromABranch($args);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $userService->createUser($user, $user->getPassword(), false);
    }
}
