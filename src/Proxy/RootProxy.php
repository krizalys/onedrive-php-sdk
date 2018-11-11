<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Root;

class RootProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Root $root
     *        The root.
     */
    public function __construct(Graph $graph, Root $root)
    {
        parent::__construct($graph, $root);
    }
}
