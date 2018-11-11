<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Video;

class VideoProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Video $video
     *        The video.
     */
    public function __construct(Graph $graph, Video $video)
    {
        parent::__construct($graph, $video);
    }
}
