<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Package;

class PackageProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Package $package
     *        The package.
     */
    public function __construct(Graph $graph, Package $package)
    {
        parent::__construct($graph, $package);
    }
}
