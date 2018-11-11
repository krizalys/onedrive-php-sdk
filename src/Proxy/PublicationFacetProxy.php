<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\PublicationFacet;

class PublicationFacetProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param PublicationFacet $publicationFacet
     *        The publication facet.
     */
    public function __construct(Graph $graph, PublicationFacet $publicationFacet)
    {
        parent::__construct($graph, $publicationFacet);
    }
}
