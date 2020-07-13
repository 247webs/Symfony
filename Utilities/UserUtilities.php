<?php

namespace AppBundle\Utilities;

use AppBundle\Entity\User;

class UserUtilities
{
    /**
     * @param User $user
     * @return array
     */
    public static function getUserBranchAndCompanyIds(User $user)
    {
        return [
            'userId'    => $user->getId(),
            'branchId'  => $user->getBranch() ? $user->getBranch()->getId() : null,
            'companyId' => $user->getBranch() && $user->getBranch()->getCompany()
                ? $user->getBranch()->getCompany()->getId() : null,
        ];
    }
}