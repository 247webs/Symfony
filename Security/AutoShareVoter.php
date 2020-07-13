<?php

namespace AppBundle\Security;

use AppBundle\Document\Sharing\AutoSharing;
use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AutoShareVoter extends Voter
{
    const VIEW              = 'view';
    const EDIT              = 'edit';

    private $decisionManager;

    private $em;

    public function __construct(AccessDecisionManagerInterface $decisionManager, EntityManager $em)
    {
        $this->decisionManager = $decisionManager;
        $this->em = $em;
    }

    public function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        return $subject instanceof AutoSharing;
    }

    public function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$token->getUser() instanceof User) {
            return false;
        }

        /** @var AutoSharing $autoSharing */
        $autoSharing = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($autoSharing, $token);
            case self::EDIT:
                return $this->canEdit($autoSharing, $token);
        }

        return false;
    }

    public function canView(AutoSharing $autoSharing, TokenInterface $token)
    {
        return $this->canEdit($autoSharing, $token);
    }

    public function canEdit(AutoSharing $autoSharing, TokenInterface $token)
    {
        $user = $token->getUser();

        /** Allow SharingProfileEditAccess to Super Admin */
        if($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        if (null !== $autoSharing->getSharingProfile()->getBranchId()) {
            return $user->getBranch()->getId() === $autoSharing->getSharingProfile()->getBranchId()
            && $this->decisionManager->decide($token, ['ROLE_BRANCH_ADMIN']);
        }

        if (null !== $autoSharing->getSharingProfile()->getCompanyId()) {
            return $user->getBranch()->getCompany()->getId() === $autoSharing->getSharingProfile()->getCompanyId()
            && $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN']);
        }

        if($autoSharing->getSharingProfile()->getUserId() === $user->getId()) {
            return true;
        }

        if($user && $user->getBranch() && $user->getBranch()->getCompany()) {

            /** @var UserRepository $userRepository */
            $userRepository = $this->em->getRepository(User::class);

            /** @var User $companyAdmin */
            $userBelongsToCompany = $userRepository->isUserBelongsToCompany($autoSharing->getSharingProfile()->getUserId(), $user->getBranch()->getCompany()->getId());

            if($userBelongsToCompany && $this->decisionManager->decide($token, ['ROLE_COMPANY_ADMIN'])) {
                return true;
            }
        }

        return false;
    }
}
