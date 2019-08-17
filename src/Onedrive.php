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

namespace Krizalys\Onedrive;

use GuzzleHttp\Client as GuzzleHttpClient;
use Krizalys\Onedrive\Parameter\DriveItemParameterDirector;
use Microsoft\Graph\Graph;

/**
 * A facade exposing main OneDrive functionality while hiding implementation
 * details.
 *
 * Currently, this class exposes only one function, static, with a limited
 * number of parameters. This allows users to create
 * {@see Client Client} instances with minimal knowledge of API internals.
 *
 * Getting started with a OneDrive client is as trivial as:
 *
 * ```php
 * $client = Onedrive::client('<YOUR_CLIENT_ID>');
 * ```
 *
 * @since 2.3.0
 */
class Onedrive
{
    /**
     * Creates a Client instance and its dependencies.
     *
     * @param string $clientId
     *        The client ID.
     * @param mixed[] $options
     *        The options to use while creating this object. Supported options:
     *          - `'state'` *(object)*: the OneDrive client state, as returned
     *            by {@see Client::getState() getState()}. Default: `[]`.
     *
     * @return Client
     *         The client created.
     */
    public static function client($clientId, array $options = [])
    {
        $graph                      = new Graph();
        $httpClient                 = new GuzzleHttpClient();
        $driveItemParameterDirector = new DriveItemParameterDirector();

        return new Client(
            $clientId,
            $graph,
            $httpClient,
            $driveItemParameterDirector,
            $options
        );
    }
}
