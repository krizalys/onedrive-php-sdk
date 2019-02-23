<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\BaseItem;

class BaseItemProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param BaseItem $baseItem
     *        The base item.
     */
    public function __construct(Graph $graph, BaseItem $baseItem)
    {
        parent::__construct($graph, $baseItem);
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
        $baseItem = $this->entity;

        switch ($name) {
            case 'createdBy':
                $createdBy = $baseItem->getCreatedBy();
                return $createdBy !== null ? new IdentitySetProxy($this->graph, $createdBy) : null;

            case 'createdDateTime':
                return $baseItem->getCreatedDateTime();

            case 'description':
                return $baseItem->getDescription();

            case 'eTag':
                return $baseItem->getETag();

            case 'lastModifiedBy':
                $lastModifiedBy = $baseItem->getLastModifiedBy();
                return $lastModifiedBy !== null ? new IdentitySetProxy($this->graph, $lastModifiedBy) : null;

            case 'lastModifiedDateTime':
                return $baseItem->getLastModifiedDateTime();

            case 'name':
                return $baseItem->getName();

            case 'parentReference':
                $parentReference = $baseItem->getParentReference();
                return $parentReference !== null ? new ItemReferenceProxy($this->graph, $parentReference) : null;

            case 'webUrl':
                return $baseItem->getWebUrl();

            case 'createdByUser':
                $createdByUser = $baseItem->getCreatedByUser();
                return $createdByUser !== null ? new UserProxy($this->graph, $createdByUser) : null;

            case 'lastModifiedByUser':
                $lastModifiedByUser = $baseItem->getLastModifiedByUser();
                return $lastModifiedByUser !== null ? new UserProxy($this->graph, $lastModifiedByUser) : null;

            default:
                return parent::__get($name);
        }
    }
}
