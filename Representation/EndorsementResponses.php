<?php

namespace AppBundle\Representation;

use JMS\Serializer\Annotation as Serializer;
use Pagerfanta\Adapter\DoctrineODMMongoDBAdapter;
use Pagerfanta\Pagerfanta;

/** @Serializer\XmlRoot("endorsement_responses") */
class EndorsementResponses implements RepresentationInterface
{
    /**
     * @Serializer\XmlKeyValuePairs
     */
    public $meta;

    /**
     * @Serializer\Type("array<AppBundle\Document\EndorsementResponse>")
     * @Serializer\XmlList(inline=true, entry="EndorsementResponse")
     * @Serializer\SerializedName("endorsement_responses")
     */
    public $data;

    public function __construct($results, $total, $limit = 100, $page = 1)
    {
        if ($limit > 100) {
            $limit = 100;
        }

        $pager = new Pagerfanta(new DoctrineODMMongoDBAdapter($results));
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($page);

        $this->addMeta('limit', $pager->getMaxPerPage());
        $this->addMeta('page', (int) $page);
        $this->addMeta('current_items', $pager->getNbResults());
        $this->addMeta('total_items', (int) $total);

        $this->data = $pager->getCurrentPageResults();
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