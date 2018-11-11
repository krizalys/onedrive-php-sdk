<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Permission;

class PermissionProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Permission $permission
     *        The permission.
     */
    public function __construct(Graph $graph, Permission $permission)
    {
        parent::__construct($graph, $permission);
    }
}
