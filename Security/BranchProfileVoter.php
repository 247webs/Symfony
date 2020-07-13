<?php

namespace AppBundle\Security;

use AppBundle\Entity\BranchProfile;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BranchProfileVoter extends Voter
{
    const VIEW              = 'view';
    const EDIT              = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof BranchProfile) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        if (!$requester instanceof User) {
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var BranchProfile $branchProfile */
        $branchProfile = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView();
            case self::EDIT:
                return $this->canEdit($branchProfile, $token);
        }

        return false;
    }

    private function canView()
    {
        return true;
    }

    private function canEdit(BranchProfile $branchProfile, TokenInterface $token)
    {
        if (!$this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN'])) {
            return false;
        }


        if ($branchProfile->getBranch()->getId() != $token->getUser()->getBranch()->getId()) {
            return false;
        }

        return true;
    }
}
