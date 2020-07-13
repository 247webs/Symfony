<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\ClioToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ClioVoter extends Voter
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

        if (!$subject instanceof ClioToken) {
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

        /** @var ClioToken $clioToken */
        $clioToken = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($clioToken, $token);
            case self::EDIT:
                return $this->canEdit($clioToken, $token);
        }

        return false;
    }

    private function canView(ClioToken $clioToken, TokenInterface $token)
    {
        return $this->canEdit($clioToken, $token);
    }

    private function canEdit(ClioToken $clioToken, TokenInterface $token)
    {
        // Company's administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $clioToken->getCompany()->getId()
        ) {
            return true;
        }

        return false;
    }
}
