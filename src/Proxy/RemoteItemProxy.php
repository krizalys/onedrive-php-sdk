<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\RemoteItem;

class RemoteItemProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param RemoteItem $remoteItem
     *        The remote item.
     */
    public function __construct(Graph $graph, RemoteItem $remoteItem)
    {
        parent::__construct($graph, $remoteItem);
    }
}
