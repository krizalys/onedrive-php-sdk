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

use Krizalys\Onedrive\Parameter\Definition\BodyParameterDefinition;
use Krizalys\Onedrive\Parameter\Definition\HeaderParameterDefinition;
use Krizalys\Onedrive\Parameter\Definition\QueryStringParameterDefinition;
use Krizalys\Onedrive\Parameter\Injector\FlatInjector;
use Krizalys\Onedrive\Parameter\Injector\HierarchicalInjector;
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
     * @var ParameterDefinitionInterface[string]
     *      The get children query string parameter definitions.
     */
    private static $getChildrenQueryStringParameterDefinitions;

    /**
     * @var ParameterDefinitionInterface[string]
     *      The create folder body parameter definitions.
     */
    private static $postChildrenBodyParameterDefinitions;

    /**
     * @var ParameterDefinitionInterface[string]
     *      The put content query string parameter definitions.
     */
    private static $putContentQueryStringParameterDefinitions;

    /**
     * @var ParameterDefinitionInterface[string]
     *      The put content header parameter definitions.
     */
    private static $putContentHeaderParameterDefinitions;

    /**
     * @var ParameterDefinitionInterface[string]
     *      The post create upload session body parameter definitions.
     */
    private static $postCreateUploadSessionBodyParameterDefinitions;

    /**
     * {@inheritDoc}
     *
     * @param mixed[string] $options
     *        The options.
     *
     * @return string[string]
     *         The parameters.
     *
     * @since 2.3.0
     */
    public function buildGetChildren(array $options)
    {
        if (self::$getChildrenQueryStringParameterDefinitions === null) {
            self::$getChildrenQueryStringParameterDefinitions = [
                'top' => new QueryStringParameterDefinition(
                    new FlatInjector('$top'),
                    new ScalarSerializer()
                ),
                'orderBy' => new QueryStringParameterDefinition(
                    new FlatInjector('$orderby'),
                    new OrderBySerializer()
                ),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$getChildrenQueryStringParameterDefinitions)
            ->setOptions($options)
            ->build();
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[string] $options
     *        The options.
     *
     * @return string[string]
     *         The parameters.
     *
     * @since 2.4.0
     */
    public function buildPostChildrenBodyParameters(array $options)
    {
        if (self::$postChildrenBodyParameterDefinitions === null) {
            self::$postChildrenBodyParameterDefinitions = [
                'conflictBehavior' => new BodyParameterDefinition(
                    new HierarchicalInjector(['@microsoft.graph.conflictBehavior']),
                    new ScalarSerializer()
                ),
                'description' => new BodyParameterDefinition(
                    new HierarchicalInjector(['description']),
                    new ScalarSerializer()
                ),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$postChildrenBodyParameterDefinitions)
            ->setOptions($options)
            ->build();
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[string] $options
     *        The options.
     *
     * @return string[string]
     *         The parameters.
     *
     * @since 2.4.0
     */
    public function buildPutContentQueryStringParameters(array $options)
    {
        if (self::$putContentQueryStringParameterDefinitions === null) {
            self::$putContentQueryStringParameterDefinitions = [
                'conflictBehavior' => new QueryStringParameterDefinition(
                    new FlatInjector('@microsoft.graph.conflictBehavior'),
                    new ScalarSerializer()
                ),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$putContentQueryStringParameterDefinitions)
            ->setOptions($options)
            ->build();
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[string] $options
     *        The options.
     *
     * @return string[string]
     *         The parameters.
     *
     * @since 2.3.0
     */
    public function buildPutContentHeaderParameters(array $options)
    {
        if (self::$putContentHeaderParameterDefinitions === null) {
            self::$putContentHeaderParameterDefinitions = [
                'contentType' => new HeaderParameterDefinition(
                    new FlatInjector('Content-Type'),
                    new ScalarSerializer()
                ),
                'Content-Type' => new HeaderParameterDefinition(
                    new FlatInjector('Content-Type'),
                    new OrderBySerializer()
                ),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$putContentHeaderParameterDefinitions)
            ->setOptions($options)
            ->build();
    }

    /**
     * {@inheritDoc}
     *
     * @param mixed[string] $options
     *        The options.
     *
     * @return string[string]
     *         The parameters.
     *
     * @since 2.4.0
     */
    public function buildPostCreateUploadSessionBodyParameters(array $options)
    {
        if (self::$postCreateUploadSessionBodyParameterDefinitions === null) {
            self::$postCreateUploadSessionBodyParameterDefinitions = [
                'conflictBehavior' => new BodyParameterDefinition(
                    new HierarchicalInjector(['item', '@microsoft.graph.conflictBehavior']),
                    new ScalarSerializer()
                ),
            ];
        }

        $builder = new ParameterBuilder();

        return $builder
            ->setParameterDefinitions(self::$postCreateUploadSessionBodyParameterDefinitions)
            ->setOptions($options)
            ->build();
    }
}
