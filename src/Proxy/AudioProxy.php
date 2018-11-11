<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Audio;

class AudioProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Audio $audio
     *        The audio.
     */
    public function __construct(Graph $graph, Audio $audio)
    {
        parent::__construct($graph, $audio);
    }
}
