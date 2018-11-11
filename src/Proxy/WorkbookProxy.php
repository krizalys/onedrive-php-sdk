<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Workbook;

class WorkbookProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Workbook $workbook
     *        The workbook.
     */
    public function __construct(Graph $graph, Workbook $workbook)
    {
        parent::__construct($graph, $workbook);
    }
}
