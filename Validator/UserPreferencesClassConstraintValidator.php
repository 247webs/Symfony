<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Preferences\UserPreferences;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserPreferencesClassConstraintValidator extends ConstraintValidator
{
    /** @var AuthorizationCheckerInterface $authChecker */
    private $authChecker;

    public function __construct(AuthorizationCheckerInterface $authChecker)
    {
        $this->authChecker = $authChecker;
    }

    /**
     * @param UserPreferences $userPreferences
     * @param Constraint $constraint
     */
    public function validate($userPreferences, Constraint $constraint)
    {
        if (!$this->defaultViewIsValid($userPreferences->getDefaultView())) {
            $this->context
                ->buildViolation('Invalid default view')
                ->atPath('default_view')
                ->setInvalidValue($userPreferences->getDefaultView())
                ->addViolation();

            return;
        }


        if (!$this->requestedViewIsAllowed($userPreferences->getUser(), $userPreferences->getDefaultView())) {
            $this->context
                ->buildViolation('Users must have branch and/or company admin rights to set this default view')
                ->atPath('default_view')
                ->setInvalidValue($userPreferences->getDefaultView())
                ->addViolation();
        }
    }

    /**
     * @param string $value
     * @return bool
     */
    private function defaultViewIsValid(string $value)
    {
        $value = strtolower($value);

        if ($value === UserPreferences::DEFAULT_VIEW_BRANCH ||
            $value === UserPreferences::DEFAULT_VIEW_COMPANY ||
            $value === UserPreferences::DEFAULT_VIEW_USER
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param User $user
     * @param string $value
     * @return bool
     */
    private function requestedViewIsAllowed(User $user, string $value)
    {
        $value = strtolower($value);

        if ($value === UserPreferences::DEFAULT_VIEW_USER) {
            return true;
        }

        if ($value === UserPreferences::DEFAULT_VIEW_BRANCH &&
            $this->authChecker->isGranted('ROLE_BRANCH_ADMIN', $user)
        ) {
            return true;
        }

        if ($value === UserPreferences::DEFAULT_VIEW_COMPANY &&
            $this->authChecker->isGranted('ROLE_COMPANY_ADMIN', $user)
        ) {
            return true;
        }

        return false;
    }
}
