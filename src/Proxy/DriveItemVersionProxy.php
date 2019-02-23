<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DriveItemVersion;

class DriveItemVersionProxy extends BaseItemVersionProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param DriveItemVersion $driveItemVersion
     *        The drive item version.
     */
    public function __construct(Graph $graph, DriveItemVersion $driveItemVersion)
    {
        parent::__construct($graph, $driveItemVersion);
    }
}
