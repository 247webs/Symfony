<?php

namespace AppBundle\Security;

use AppBundle\Document\Sharing\VerificationNoticePriority;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VerificationNoticePriorityVoter extends Voter
{
    const VIEW              = 'view';
    const EDIT              = 'edit';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    public function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        return $subject instanceof VerificationNoticePriority;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        /** @var VerificationNoticePriority $verificationNoticePriority */
        $verificationNoticePriority = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($verificationNoticePriority, $token);
            case self::EDIT:
                return $this->canEdit($verificationNoticePriority, $token);
        }

        return false;
    }

    public function canView(VerificationNoticePriority $verificationNoticePriority, TokenInterface $token)
    {
        return $this->canEdit($verificationNoticePriority, $token);
    }

    public function canEdit(VerificationNoticePriority $verificationNoticePriority, TokenInterface $token)
    {
        $user = $token->getUser();

        if (null !== $verificationNoticePriority->getSharingProfile()->getBranchId()) {
            return $user->getBranch()->getId() === $verificationNoticePriority->getSharingProfile()->getBranchId()
            && $this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']);
        }

        if (null !== $verificationNoticePriority->getSharingProfile()->getCompanyId()) {
            return $user->getBranch()->getCompany()->getId() === $verificationNoticePriority->getSharingProfile()->getCompanyId()
            && $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']);
        }

        return $verificationNoticePriority->getSharingProfile()->getUserId() === $user->getId();
    }
}
