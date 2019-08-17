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

namespace Krizalys\Onedrive\Parameter;

use Krizalys\Onedrive\Parameter\Definition\ParameterDefinitionInterface;

/**
 * An interface defining the contract for a parameter builder.
 *
 * @since 2.3.0
 */
interface ParameterBuilderInterface
{
    /**
     * Sets parameter definitions to this instance.
     *
     * @param array<string, ParameterDefinitionInterface> $parameterDefinitions
     *        The parameter definitions.
     *
     * @return ParameterBuilderInterface
     *         This instance.
     *
     * @since 2.3.0
     */
    public function setParameterDefinitions(array $parameterDefinitions);

    /**
     * Sets options to this instance.
     *
     * @param array<string, mixed> $options
     *        The options.
     *
     * @return ParameterBuilderInterface
     *         This instance.
     *
     * @since 2.3.0
     */
    public function setOptions(array $options);

    /**
     * Builds parameters from this instance.
     *
     * @return array<string, string>
     *         The parameters.
     *
     * @since 2.3.0
     */
    public function build();
}
