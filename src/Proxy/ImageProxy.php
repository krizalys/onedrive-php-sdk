<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Image;

class ImageProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Image $image
     *        The image.
     */
    public function __construct(Graph $graph, Image $image)
    {
        parent::__construct($graph, $image);
    }
}
