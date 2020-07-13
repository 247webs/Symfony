<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Company;
use AppBundle\Repository\CompanyRepository;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class CompanySerializerSubscriber
 * @package AppBundle\EventListener
 */
class CompanySerializerSubscriber implements EventSubscriberInterface
{
    /** @var CompanyRepository */
    private $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'method' => 'onPostSerialize',
                'class' => Company::class
            ]
        ];
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();

        $visitor->setData(
            'is_enterprise_customer',
            $this->companyRepository->isEnterpriseCustomer($event->getObject())
        );
    }
}
