<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\ItemReference;

class ItemReferenceProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param ItemReference $itemReference
     *        The item reference.
     */
    public function __construct(Graph $graph, ItemReference $itemReference)
    {
        parent::__construct($graph, $itemReference);
    }

    /**
     * Getter.
     *
     * @param string $name
     *        The name.
     *
     * @return mixed
     *         The value.
     */
    public function __get($name)
    {
        $itemReference = $this->entity;

        switch ($name) {
            case 'id':
                return $itemReference->getId();

            case 'driveId':
                return $itemReference->getDriveId();

            case 'driveType':
                return $itemReference->getDriveType();

            case 'path':
                return $itemReference->getPath();

            default:
                return parent::__get($name);
        }
    }
}
