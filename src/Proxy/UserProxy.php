<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\User;

class UserProxy extends DirectoryObjectProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param User $user
     *        The user.
     */
    public function __construct(Graph $graph, User $user)
    {
        parent::__construct($graph, $user);
    }
}
