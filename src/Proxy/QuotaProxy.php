<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Quota;

class QuotaProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Quota $quota
     *        The quota.
     */
    public function __construct(Graph $graph, Quota $quota)
    {
        parent::__construct($graph, $quota);
    }

    /**
     * Getter.
     *
     * @param string $name
     *        The name.
     *
     * @return mixed
     *         The value.
     */
    public function __get($name)
    {
        $quota = $this->entity;

        switch ($name) {
            case 'deleted':
                return $quota->getDeleted();

            case 'remaining':
                return $quota->getRemaining();

            case 'state':
                return $quota->getState();

            case 'total':
                return $quota->getTotal();

            case 'used':
                return $quota->getUsed();

            default:
                throw new \Exception("Undefined property: $name");
        }
    }
}
