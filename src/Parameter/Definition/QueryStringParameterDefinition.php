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

namespace Krizalys\Onedrive\Parameter\Definition;

/**
 * An implementation for a query string parameter definition.
 *
 * These parameters always have their name prefixed by '$', as recommended by
 * the OneDrive documentation.
 *
 * @since 2.3.0
 */
class QueryStringParameterDefinition extends AbstractParameterDefinition
{
    /**
     * {@inheritdoc}
     *
     * @return string
     *         The serialized key.
     *
     * @since 2.3.0
     */
    public function serializeKey()
    {
        return "\${$this->name}";
    }
}
