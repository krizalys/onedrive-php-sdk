<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\DirectoryObject;

class DirectoryObjectProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param DirectoryObject $directoryObject
     *        The directory object.
     */
    public function __construct(Graph $graph, DirectoryObject $directoryObject)
    {
        parent::__construct($graph, $directoryObject);
    }
}
