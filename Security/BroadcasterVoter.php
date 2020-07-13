<?php

namespace AppBundle\Security;

use AppBundle\Document\Sharing\Broadcaster\BroadcasterAbstract;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BroadcasterVoter extends Voter
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

        return $subject instanceof BroadcasterAbstract;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        /** @var BroadcasterAbstract $broadcaster */
        $broadcaster = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($broadcaster, $token);
            case self::EDIT:
                return $this->canEdit($broadcaster, $token);
        }

        return false;
    }

    public function canView(BroadcasterAbstract $broadcaster, TokenInterface $token)
    {
        return $this->canEdit($broadcaster, $token);
    }

    public function canEdit(BroadcasterAbstract $broadcaster, TokenInterface $token)
    {
        $user = $token->getUser();

        if (null !== $broadcaster->getSharingProfile()->getBranchId()) {
            return $user->getBranch()->getId() === $broadcaster->getSharingProfile()->getBranchId()
            && $this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']);
        }

        if (null !== $broadcaster->getSharingProfile()->getCompanyId()) {
            return $user->getBranch()->getCompany()->getId() === $broadcaster->getSharingProfile()->getCompanyId()
            && $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']);
        }

        return $broadcaster->getSharingProfile()->getUserId() === $user->getId();
    }
}
