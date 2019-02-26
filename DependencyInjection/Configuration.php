<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 *
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\DependencyInjection;

use Closure;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use function array_diff;
use function implode;
use function is_subclass_of;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('linkin_swagger_resolver');

        $rootNode
            ->children()
                ->arrayNode('enable_normalization')
                    ->scalarPrototype()
                        ->defaultValue(['query', 'path', 'header'])
                    ->end()
                    ->validate()->always($this->validationForEnableNormalization())->end()
                ->end()
                ->scalarNode('path_merge_strategy')
                    ->cannotBeEmpty()
                    ->defaultValue(StrictMergeStrategy::class)
                    ->validate()
                        ->always($this->validationForPathMergeStrategy())
                    ->end()
                ->end()
                ->scalarNode('configuration_loader_service')->defaultNull()->end()
                ->scalarNode('configuration_file')->defaultNull()->end()
                ->arrayNode('swagger_php')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('scan')->defaultNull()->end()
                        ->scalarNode('exclude')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return Closure
     */
    private function validationForPathMergeStrategy(): Closure
    {
        return function ($className) {
            if (!is_subclass_of($className, MergeStrategyInterface::class)) {
                throw new InvalidConfigurationException(sprintf(
                    'Parameter "path_merge_strategy" should contain class which implements "%s"',
                    MergeStrategyInterface::class
                ));
            }

            return $className;
        };
    }

    /**
     * @return Closure
     */
    private function validationForEnableNormalization(): Closure
    {
        return function ($fromValues) {
            $allowedValues = ['query', 'path', 'header', 'body', 'formData'];

            $diff = array_diff($fromValues, $allowedValues);

            if (empty($diff)) {
                return $fromValues;
            }

            throw new InvalidConfigurationException(sprintf(
                'Parameter "enable_normalization" do not support parameters: "%s". Allowed values: "%s"',
                implode(', ', $diff),
                implode(', ', $allowedValues)
            ));
        };
    }
}
