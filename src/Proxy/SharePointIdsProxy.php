<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\SharePointIds;

class SharePointIdsProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param SharePointIds $sharePointIds
     *        The SharePoint IDs.
     */
    public function __construct(Graph $graph, SharePointIds $sharePointIds)
    {
        parent::__construct($graph, $sharePointIds);
    }
}
