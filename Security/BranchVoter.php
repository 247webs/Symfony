<?php

namespace AppBundle\Security;

use AppBundle\Entity\Branch;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BranchVoter extends Voter
{
    const VIEW              = 'view';
    const VIEW_LIST         = 'list';
    const EDIT              = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::VIEW_LIST, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Branch) {
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

        /** @var Branch $branch */
        $branch = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($branch, $token);
            case self::VIEW_LIST:
                return $this->canEdit($branch, $token);
            case self::EDIT:
                return $this->canEdit($branch, $token);
        }

        return false;
    }

    private function canView(Branch $branch, TokenInterface $token)
    {
        // Company administrators can view
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $branch->getCompany()->getId()
        ) {
            return true;
        }

        // Branch's users are granted access
        if ($token->getUser()->getBranch()->getId() == $branch->getId()) {
            return true;
        }

        return false;
    }

    private function canEdit(Branch $branch, TokenInterface $token)
    {
        // Company administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $branch->getCompany()->getId()
        ) {
            return true;
        }

        // Branch's administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']) &&
            $token->getUser()->getBranch()->getId() == $branch->getId()
        ) {
            return true;
        }

        return false;
    }
}
