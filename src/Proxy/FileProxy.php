<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\File;

class FileProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param File $file
     *        The file.
     */
    public function __construct(Graph $graph, File $file)
    {
        parent::__construct($graph, $file);
    }
}
