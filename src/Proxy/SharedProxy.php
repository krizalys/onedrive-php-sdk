<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Shared;

class SharedProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Shared $shared
     *        The shared.
     */
    public function __construct(Graph $graph, Shared $shared)
    {
        parent::__construct($graph, $shared);
    }
}
