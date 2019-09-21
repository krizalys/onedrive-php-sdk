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
use Microsoft\Graph\Model\Photo;

/**
 * A proxy to a \Microsoft\Graph\Model\Photo instance.
 *
 * @since 2.0.0
 *
 * @api
 *
 * @link https://github.com/microsoftgraph/msgraph-sdk-php/blob/dev/src/Model/Photo.php
 */
class PhotoProxy extends EntityProxy
{
    /**
     * Constructor.
     *
     * @param \Microsoft\Graph\Graph $graph
     *        The Microsoft Graph.
     * @param \Microsoft\Graph\Model\Photo $photo
     *        The photo.
     *
     * @since 2.0.0
     */
    public function __construct(Graph $graph, Photo $photo)
    {
        parent::__construct($graph, $photo);
    }
}
