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

use Krizalys\Onedrive\Parameter\Definition\HeaderParameterDefinition;
use Krizalys\Onedrive\Parameter\Definition\QueryStringParameterDefinition;
use Krizalys\Onedrive\Serializer\OrderBySerializer;
use Krizalys\Onedrive\Serializer\ScalarSerializer;

/**
 * A class to instantiate parameter builders.
 *
 * @since 2.3.0
 */
class DriveItemParameterDirector implements DriveItemParameterDirectorInterface
{
    /**
     * @var array<string, ParameterDefinitionInterface>
     *      The get children parameter definitions.
     */
    private static $getChildrenParameterDefinitions;

    /**
     * @var array<string, ParameterDefinitionInterface>
     *      The put content parameter definitions.
     */
    private static $putContentParameterDefinitions;

    /**
     * {@inheritdoc}
     */
    public function buildGetChildren(array $options)
    {
        if (self::$getChildrenParameterDefinitions === null) {
            self::$getChildrenParameterDefinitions = [
                'top'     => new QueryStringParameterDefinition('top', new ScalarSerializer()),
                'orderby' => new QueryStringParameterDefinition('orderby', new OrderBySerializer()),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$getChildrenParameterDefinitions)
            ->setOptions($options)
            ->build();
    }

    /**
     * {@inheritdoc}
     */
    public function buildPutContent(array $options)
    {
        if (self::$putContentParameterDefinitions === null) {
            self::$putContentParameterDefinitions = [
                'contentType'  => new HeaderParameterDefinition('Content-Type', new ScalarSerializer()),
                'Content-Type' => new HeaderParameterDefinition('Content-Type', new OrderBySerializer()),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$putContentParameterDefinitions)
            ->setOptions($options)
            ->build();
    }
}
