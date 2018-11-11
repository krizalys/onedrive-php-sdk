<?php

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\SpecialFolder;

class SpecialFolderProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The graph.
     * @param SpecialFolder $specialFolder
     *        The special folder.
     */
    public function __construct(Graph $graph, SpecialFolder $specialFolder)
    {
        parent::__construct($graph, $specialFolder);
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
        $specialFolder = $this->entity;

        switch ($name) {
            case 'name':
                return $specialFolder->getName();

            default:
                return parent::__get($name);
        }
    }
}
