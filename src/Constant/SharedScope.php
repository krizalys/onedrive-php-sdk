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

namespace Krizalys\Onedrive\Constant;

/**
 * The shared scope.
 *
 * @since 2.5.0
 *
 * @api
 */
class SharedScope
{
    /**
     * @var string
     *      Anonymous scope.
     *
     * @since 2.5.0
     *
     * @api
     */
    const ANONYMOUS = 'anonymous';

    /**
     * @var string
     *      Organization scope.
     *
     * @since 2.5.0
     *
     * @api
     */
    const ORGANIZATION = 'organization';

    /**
     * @var string
     *      Users scope.
     *
     * @since 2.5.0
     *
     * @api
     */
    const USERS = 'users';
}
