<?php

namespace AppBundle\Security;

use AppBundle\Entity\Survey;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SurveyVoter extends Voter
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

        return $subject instanceof Survey;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var Survey $survey */
        $survey = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($survey, $token);
            case self::EDIT:
                return $this->canEdit($survey, $token);
        }

        return false;
    }

    public function canView(Survey $survey, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();

        /** @var User $surveyOwner */
        $surveyOwner = $survey->getUser();

        $isCompanyAdmin = $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']);
        $isBranchAdmin = $this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']);

        if ($surveyOwner->getBranch() && $user->getBranch()) {
            if ($isCompanyAdmin) {
                return $user->getBranch()->getCompany()->getId() === $surveyOwner->getBranch()->getCompany()->getId();
            }

            if ($isBranchAdmin) {
                return $user->getBranch()->getId() === $surveyOwner->getBranch()->getId();
            }
        }

        return $survey->getUser()->getId() === $user->getId();
    }

    public function canEdit(Survey $survey, TokenInterface $token)
    {
        $user = $token->getUser();

        return $survey->getUser()->getId() === $user->getId();
    }
}
