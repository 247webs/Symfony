<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\SalesforceToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SalesforceVoter extends Voter
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

        if (!$subject instanceof SalesforceToken) {
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

        /** @var SalesforceToken $salesforceToken */
        $salesforceToken = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($salesforceToken, $token);
            case self::EDIT:
                return $this->canEdit($salesforceToken, $token);
        }

        return false;
    }

    private function canView(SalesforceToken $salesforceToken, TokenInterface $token)
    {
        return $this->canEdit($salesforceToken, $token);
    }

    private function canEdit(SalesforceToken $salesforceToken, TokenInterface $token)
    {
        // Company's administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $salesforceToken->getCompany()->getId()
        ) {
            return true;
        }

        return false;
    }
}
