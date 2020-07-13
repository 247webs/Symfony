<?php

namespace AppBundle\Representation;

use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/** @Serializer\XmlRoot("resellers") */
class Resellers implements RepresentationInterface
{
    /**
     * @Serializer\XmlKeyValuePairs
     */
    public $meta;

    /**
     * @Serializer\Type("array<AppBundle\Entity\Reseller>")
     * @Serializer\XmlList(inline=true, entry = "reseller")
     * @Serializer\SerializedName("resellers")
     * @Serializer\Groups("private")
     */
    public $data;

    public function __construct($query, $total, $limit = 100, $page = 1)
    {
        if ($limit > 100) {
            $limit = 100;
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($query));
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($page);

        $this->addMeta('limit', $pager->getMaxPerPage());
        $this->addMeta('page', $pager->getCurrentPageOffsetStart());
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