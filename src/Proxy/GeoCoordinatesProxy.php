<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\GeoCoordinates;

class GeoCoordinatesProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param GeoCoordinates $geoCoordinates
     *        The geo coordinates.
     */
    public function __construct(Graph $graph, GeoCoordinates $geoCoordinates)
    {
        parent::__construct($graph, $geoCoordinates);
    }
}
