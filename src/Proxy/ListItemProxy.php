<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\ListItem;

class ListItemProxy extends BaseItemProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param ListItem $listItem
     *        The list item.
     */
    public function __construct(Graph $graph, ListItem $listItem)
    {
        parent::__construct($graph, $listItem);
    }
}
