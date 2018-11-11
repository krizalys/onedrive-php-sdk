<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Photo;

class PhotoProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Photo $photo
     *        The photo.
     */
    public function __construct(Graph $graph, Photo $photo)
    {
        parent::__construct($graph, $photo);
    }
}
