<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\SearchResult;

class SearchResultProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param SearchResult $searchResult
     *        The search result.
     */
    public function __construct(Graph $graph, SearchResult $searchResult)
    {
        parent::__construct($graph, $searchResult);
    }
}
