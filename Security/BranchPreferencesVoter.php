<?php

namespace AppBundle\Security;

use AppBundle\Entity\Preferences\BranchPreferences;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BranchPreferencesVoter extends Voter
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

        return $subject instanceof BranchPreferences;
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

        /** @var BranchPreferences $branchPreferences */
        $branchPreferences = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($branchPreferences, $token);
            case self::EDIT:
                return $this->canEdit($branchPreferences, $token);
        }

        return false;
    }

    private function canView(BranchPreferences $preferences, TokenInterface $token)
    {
        return $this->canEdit($preferences, $token);
    }

    private function canEdit(BranchPreferences $preferences, TokenInterface $token)
    {
        if (!$this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN'])) {
            return false;
        }

        return $preferences->getBranch()->getId() == $token->getUser()->getBranch()->getId();
    }
}
