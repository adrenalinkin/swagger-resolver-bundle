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
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterLocationEnum;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
        $treeBuilder = new TreeBuilder('linkin_swagger_resolver');

        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('linkin_swagger_resolver');
        }

        $rootNode
            ->children()
                ->arrayNode('enable_normalization')
                    ->enumPrototype()
                        ->values(ParameterLocationEnum::getAll())
                    ->end()
                    ->defaultValue([
                        ParameterLocationEnum::IN_QUERY,
                        ParameterLocationEnum::IN_PATH,
                        ParameterLocationEnum::IN_HEADER,
                    ])
                ->end()
                ->scalarNode('path_merge_strategy')
                    ->cannotBeEmpty()
                    ->defaultValue(StrictMergeStrategy::class)
                    ->validate()
                        ->always($this->validationForPathMergeStrategy())
                    ->end()
                ->end()
                ->scalarNode('configuration_loader_service')
                    ->defaultNull()
                    ->validate()
                        ->always($this->validationForConfigurationLoader())
                    ->end()
                ->end()
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

    private function validationForConfigurationLoader(): Closure
    {
        return static function ($className) {
            if (null === $className) {
                return $className;
            }

            if (!is_subclass_of($className, SwaggerConfigurationLoaderInterface::class)) {
                throw new InvalidConfigurationException(sprintf('Parameter "configuration_loader_service" should contain class which implements "%s"', SwaggerConfigurationLoaderInterface::class));
            }

            return $className;
        };
    }

    private function validationForPathMergeStrategy(): Closure
    {
        return static function ($className) {
            if (!is_subclass_of($className, MergeStrategyInterface::class)) {
                throw new InvalidConfigurationException(sprintf('Parameter "path_merge_strategy" should contain class which implements "%s"', MergeStrategyInterface::class));
            }

            return $className;
        };
    }
}
