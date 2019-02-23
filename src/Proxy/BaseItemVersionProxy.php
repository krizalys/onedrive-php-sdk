<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\BaseItemVersion;

class BaseItemVersionProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param BaseItemVersion $baseItemVersion
     *        The base item version.
     */
    public function __construct(Graph $graph, BaseItemVersion $baseItemVersion)
    {
        parent::__construct($graph, $baseItemVersion);
    }
}
