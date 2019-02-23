<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\SharepointIds;

class SharepointIdsProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param SharepointIds $sharepointIds
     *        The SharePoint IDs.
     */
    public function __construct(Graph $graph, SharepointIds $sharepointIds)
    {
        parent::__construct($graph, $sharepointIds);
    }
}
