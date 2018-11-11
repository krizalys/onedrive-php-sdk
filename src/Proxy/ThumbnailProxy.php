<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Thumbnail;

class ThumbnailProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Thumbnail $thumbnail
     *        The thumbnail.
     */
    public function __construct(Graph $graph, Thumbnail $thumbnail)
    {
        parent::__construct($graph, $thumbnail);
    }
}
