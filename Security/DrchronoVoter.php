<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Entity\DrchronoPractice;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DrchronoVoter extends Voter
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

        if (!$subject instanceof DrchronoPractice) {
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

        /** @var DrchronoPractice $drchronoPractice */
        $drchronoPractice = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($drchronoPractice, $token);
            case self::EDIT:
                return $this->canEdit($drchronoPractice, $token);
        }

        return false;
    }

    private function canView(DrchronoPractice $drchronoPractice, TokenInterface $token)
    {
        return $this->canEdit($drchronoPractice, $token);
    }

    private function canEdit(DrchronoPractice $drchronoPractice, TokenInterface $token)
    {
        // Company's administrators can edit
        if ($this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']) &&
            $token->getUser()->getBranch()->getCompany()->getId() == $drchronoPractice->getCompany()->getId()
        ) {
            return true;
        }

        return false;
    }
}
