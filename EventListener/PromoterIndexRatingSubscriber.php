<?php

namespace AppBundle\EventListener;

use AppBundle\Document\Answer\PromoterIndexAnswer;
use AppBundle\Document\Answer\StarRatingAnswer;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;

class PromoterIndexRatingSubscriber implements EventSubscriber
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * StarRatingSubscriber constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist',
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var PromoterIndexAnswer $promoterIndexAnswer */
        if (($promoterIndexAnswer = $args->getObject()) instanceof PromoterIndexAnswer &&
            null === $promoterIndexAnswer->getUserId()
        ) {
            /** @var User $user */
            $user = $this->em->getRepository(SurveyQuestion::class)
                ->find($promoterIndexAnswer->getQuestionId())
                ->getSurvey()
                ->getUser();

            $promoterIndexAnswer->setUserId($user->getId());
            $promoterIndexAnswer->setBranchId($user->getBranch() ? $user->getBranch()->getId() : null);
            $promoterIndexAnswer->setCompanyId($user->getBranch() && $user->getBranch()->getCompany() ?
                $user->getBranch()->getCompany()->getId() : null);
        }
    }
}
