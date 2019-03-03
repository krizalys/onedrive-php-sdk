<?php

/**
 * This file is part of Krizalys' OneDrive SDK for PHP.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @author    Christophe Vidal
 * @copyright 2008-2019 Christophe Vidal (http://www.krizalys.com)
 * @license   https://opensource.org/licenses/BSD-3-Clause 3-Clause BSD License
 * @link      https://github.com/krizalys/onedrive-php-sdk
 */

namespace Krizalys\Onedrive\Proxy;

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\PublicationFacet;

/**
 * A proxy to a \Microsoft\Graph\Model\PublicationFacet instance.
 *
 * @since 2.0.0
 *
 * @link https://github.com/microsoftgraph/msgraph-sdk-php/blob/dev/src/Model/PublicationFacet.php
 */
class PublicationFacetProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param Graph $graph
     *        The Microsoft Graph.
     * @param PublicationFacet $publicationFacet
     *        The publication facet.
     *
     * @since 2.0.0
     */
    public function __construct(Graph $graph, PublicationFacet $publicationFacet)
    {
        parent::__construct($graph, $publicationFacet);
    }
}
