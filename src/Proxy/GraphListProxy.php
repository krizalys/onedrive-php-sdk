<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\GraphList;

class GraphListProxy extends BaseItemProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param GraphList $graphList
     *        The graph list.
     */
    public function __construct(Graph $graph, GraphList $graphList)
    {
        parent::__construct($graph, $graphList);
    }
}
