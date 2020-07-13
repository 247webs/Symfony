<?php

namespace AppBundle\Security;

use AppBundle\Document\Sharing\PostSurveyPriority;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostSurveyPriorityVoter extends Voter
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

        return $subject instanceof PostSurveyPriority;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        /** @var PostSurveyPriority $postSurveyPriority */
        $postSurveyPriority = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($postSurveyPriority, $token);
            case self::EDIT:
                return $this->canEdit($postSurveyPriority, $token);
        }

        return false;
    }

    public function canView(PostSurveyPriority $postSurveyPriority, TokenInterface $token)
    {
        return $this->canEdit($postSurveyPriority, $token);
    }

    public function canEdit(PostSurveyPriority $postSurveyPriority, TokenInterface $token)
    {
        $user = $token->getUser();

        if (null !== $postSurveyPriority->getSharingProfile()->getBranchId()) {
            return $user->getBranch()->getId() === $postSurveyPriority->getSharingProfile()->getBranchId()
            && $this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']);
        }

        if (null !== $postSurveyPriority->getSharingProfile()->getCompanyId()) {
            return $user->getBranch()->getCompany()->getId() === $postSurveyPriority->getSharingProfile()->getCompanyId()
            && $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']);
        }

        return $postSurveyPriority->getSharingProfile()->getUserId() === $user->getId();
    }
}
