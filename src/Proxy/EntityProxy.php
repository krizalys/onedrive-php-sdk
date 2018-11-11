<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Entity;

class EntityProxy
{
    /**
     * @var Graph
     *      The graph.
     */
    protected $graph;

    /**
     * @var Entity
     *      The entity.
     */
    protected $entity;

    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param Entity $entity
     *        The entity.
     */
    public function __construct(Graph $graph, Entity $entity)
    {
        $this->graph  = $graph;
        $this->entity = $entity;
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
        switch ($name) {
            case 'id':
                return $this->entity->getId();

            default:
                throw new \Exception("Undefined property: $name");
        }
    }
}
