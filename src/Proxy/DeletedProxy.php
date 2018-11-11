<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Deleted;

class DeletedProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Deleted $deleted
     *        The deleted.
     */
    public function __construct(Graph $graph, Deleted $deleted)
    {
        parent::__construct($graph, $deleted);
    }
}
