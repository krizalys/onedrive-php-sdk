<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\IdentitySet;

class IdentitySetProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param IdentitySet $identitySet
     *        The identity set.
     */
    public function __construct(Graph $graph, IdentitySet $identitySet)
    {
        parent::__construct($graph, $identitySet);
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
        $identitySet = $this->entity;

        switch ($name) {
            case 'application':
                $application = $identitySet->getApplication();
                return $application !== null ? new IdentityProxy($this->graph, $application) : null;

            case 'device':
                $device = $identitySet->getDevice();
                return $device !== null ? new IdentityProxy($this->graph, $device) : null;

            case 'user':
                $user = $identitySet->getUser();
                return $user !== null ? new IdentityProxy($this->graph, $user) : null;

            default:
                return parent::__get($name);
        }
    }
}
