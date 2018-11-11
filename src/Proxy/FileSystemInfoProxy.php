<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\FileSystemInfo;

class FileSystemInfoProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param FileSystemInfo $fileSystemInfo
     *        The file system info.
     */
    public function __construct(Graph $graph, FileSystemInfo $fileSystemInfo)
    {
        parent::__construct($graph, $fileSystemInfo);
    }
}
