<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Folder;

class FolderProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Folder $folder
     *        The folder.
     */
    public function __construct(Graph $graph, Folder $folder)
    {
        parent::__construct($graph, $folder);
    }
}
