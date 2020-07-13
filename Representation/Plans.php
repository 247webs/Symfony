<?php

namespace AppBundle\Representation;

use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/** @Serializer\XmlRoot("plans") */
class Plans implements RepresentationInterface
{
    /**
     * @Serializer\XmlKeyValuePairs
     */
    public $meta;

    /**
     * @Serializer\Type("array<AppBundle\Entity\Plan>")
     * @Serializer\XmlList(inline=true, entry = "Plan")
     * @Serializer\SerializedName("plans")
     */
    public $data;

    public function __construct($query, $total)
    {
        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage(1000);
        $pager->setCurrentPage(1);

        $this->addMeta('offset', $pager->getCurrentPageOffsetStart());
        $this->addMeta('current_items', $pager->getNbResults());
        $this->addMeta('total_items', (int) $total);

        $this->data = $pager;
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMeta($key)
    {
        return $this->meta[$key];
    }
}