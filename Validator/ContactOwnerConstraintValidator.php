<?php

namespace AppBundle\Validator;

use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\Contact;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ContactOwnerConstraintValidator extends ConstraintValidator
{
    /** @var TokenStorage $token */
    private $token;

    /** @var AccessDecisionManagerInterface $decisionManager */
    private $decisionManager;

    public function __construct(TokenStorage $token, AccessDecisionManagerInterface $decisionManager)
    {
        $this->token = $token;
        $this->decisionManager = $decisionManager;
    }

    /**
     * @param Contact $contact
     * @param Constraint $constraint
     */
    public function validate($contact, Constraint $constraint)
    {
        if (null !== $this->token->getToken() && $contact->getUser() instanceof User) {
            if ($this->decisionManager->decide($this->token->getToken(), ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN'])) {
                return;
            }

            /** @var User $user */
            $user = $this->token->getToken()->getUser();

            /** @var Branch $userBranch */
            $userBranch = $user->getBranch();

            /** @var Company $userCompany */
            $userCompany = $userBranch->getCompany();

            $isCompanyAdmin = $this->decisionManager->decide($this->token->getToken(), ['ROLE_COMPANY_ADMIN']);
            $isBranchAdmin = $this->decisionManager->decide($this->token->getToken(), ['ROLE_BRANCH_ADMIN']);

            if ($isCompanyAdmin) {
                if ($contact->getUser()->getBranch()->getCompany() !== $userCompany) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('user')
                        ->addViolation();
                }

                return;
            }

            if ($isBranchAdmin) {
                if ($contact->getUser()->getBranch() !== $userBranch) {
                    $this->context->buildViolation($constraint->message)
                        ->atPath('user')
                        ->addViolation();
                }

                return;
            }


            if ($contact->getUser() !== $user) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('user')
                    ->addViolation();
            }
        }
    }
}
