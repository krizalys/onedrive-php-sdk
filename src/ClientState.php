<?php

/**
 * This file is part of Krizalys' OneDrive SDK for PHP.
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 *
 * @author    Christophe Vidal
 * @copyright 2008-2023 Christophe Vidal (http://www.krizalys.com)
 * @license   https://opensource.org/licenses/BSD-3-Clause 3-Clause BSD License
 * @link      https://github.com/krizalys/onedrive-php-sdk
 */

declare(strict_types=1);

namespace Krizalys\Onedrive;

/**
 * A client state to hold data involved in the OAuth authorization flow.
 *
 * @property string $redirectUri
 *           The redirect URI.
 * @property object $token
 *           The token.
 *
 * @since 3.0.0
 *
 * @api
 */
class ClientState
{
    /**
     * @var string
     *      The redirect URI.
     */
    private $redirectUri;

    /**
     * @var string
     *      The token.
     */
    private $token;

    /**
     * Getter.
     *
     * @param string $name
     *        The name.
     *
     * @return mixed
     *         The value.
     *
     * @since 3.0.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'redirectUri':
                return $this->redirectUri;

            case 'token':
                return $this->token;
        }
    }

    /**
     * Setter.
     *
     * @param string $name
     *        The name.
     *
     * @param mixed $value
     *        The value.
     *
     * @since 3.0.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'redirectUri':
                $this->redirectUri = $value;
                break;

            case 'token':
                $this->token = $value;
                break;
        }
    }
}
