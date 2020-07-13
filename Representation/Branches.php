<?php

namespace AppBundle\Representation;

use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/** @Serializer\XmlRoot("branches") */
class Branches implements RepresentationInterface
{
    /**
     * @Serializer\XmlKeyValuePairs
     */
    public $meta;

    /**
     * @Serializer\Type("array<AppBundle\Entity\Branch>")
     * @Serializer\XmlList(inline=true, entry = "Branch")
     * @Serializer\SerializedName("branches")
     */
    public $data;

    public function __construct($query, $total, $limit = 25, $page = 1)
    {
        if ($limit > 250) {
            $limit = 250;
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($page);

        $this->addMeta('limit', $pager->getMaxPerPage());
        $this->addMeta('page', (int) $page);
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