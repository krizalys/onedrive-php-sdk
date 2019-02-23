<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\SystemFacet;

class SystemFacetProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param SystemFacet $systemFacet
     *        The system facet.
     */
    public function __construct(Graph $graph, SystemFacet $systemFacet)
    {
        parent::__construct($graph, $systemFacet);
    }
}
