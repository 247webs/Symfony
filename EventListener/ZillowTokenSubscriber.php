<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\ReviewAggregationToken\ZillowNmlsidToken;
use AppBundle\Entity\ReviewAggregationToken\ZillowScreenNameToken;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use FOS\HttpCacheBundle\CacheManager;

/**
 * Class ZillowTokenSubscriber
 * @package AppBundle\EventListener
 */
class ZillowTokenSubscriber implements EventSubscriber
{
    /** @var CacheManager */
    private $cm;

    public function __construct(CacheManager $cm)
    {
        $this->cm = $cm;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->clearCache($args);
    }

    private function clearCache(LifecycleEventArgs $args)
    {
        $token = $args->getEntity();

        if ($token instanceof ZillowNmlsidToken || $token instanceof ZillowScreenNameToken) {
            // Invalidate caches for company, branch or user feeds

            if (null !== $token->getUser()) {
                $this->cm
                    ->invalidateRegex('/user/' . $token->getUser()->getSlug() . '/endorsements');
            }

            if (null !== $token->getBranch()) {
                $this->cm
                    ->invalidateRegex('/branch/' . $token->getBranch()->getSlug() . '/endorsements');
            }

            if (null !== $token->getCompany()) {
                $this->cm
                    ->invalidateRegex('/company/' . $token->getCompany()->getSlug() . '/endorsements');
            }
        }
    }
}
