<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Identity;

class IdentityProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Identity $identity
     *        The identity.
     */
    public function __construct(Graph $graph, Identity $identity)
    {
        parent::__construct($graph, $identity);
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
        $identity = $this->entity;

        switch ($name) {
            case 'displayName':
                return $identity->getDisplayName();

//            case 'thumbnails": [{ "@odata.type": "microsoft.graph.thumbnailSet" }]

            default:
                return parent::__get($name);
        }
    }
}
