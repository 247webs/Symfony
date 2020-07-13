<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\AthenaPractice;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AthenaPracticeVoter extends Voter
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

        if (!$subject instanceof AthenaPractice) {
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

        /** @var AthenaPractice $practice */
        $practice = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($practice, $token);
            case self::EDIT:
                return $this->canEdit($practice, $token);
        }

        return false;
    }

    private function canView(AthenaPractice $practice, TokenInterface $token)
    {
        return $this->canEdit($practice, $token);
    }

    private function canEdit(AthenaPractice $practice, TokenInterface $token)
    {
        // Company's administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $practice->getCompany()->getId()
        ) {
            return true;
        }

        return false;
    }
}
