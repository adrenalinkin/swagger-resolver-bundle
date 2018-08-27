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

use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinSwaggerResolverExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->registerConfigurationLoader($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function registerConfigurationLoader(ContainerBuilder $container, array $config): void
    {
        if (!empty($config['configuration_loader_service'])) {
            $container->setParameter(
                'linkin_swagger_resolver.configuration_loader',
                $config['configuration_loader_service']
            );

            return;
        }

        $configurationLoaderDefinitionId = 'linkin_swagger_resolver.loader.configuration';
        $container->setParameter('linkin_swagger_resolver.configuration_loader', $configurationLoaderDefinitionId);

        $bundles = $container->getParameter('kernel.bundles');

        if (empty($bundles['NelmioApiDocBundle'])) {
            $loaderDefinition = $this->getConfigurationLoaderDefinition($container, $config);
            $container->setDefinition($configurationLoaderDefinitionId, $loaderDefinition);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return Definition
     */
    private function getConfigurationLoaderDefinition(ContainerBuilder $container, array $config): Definition
    {
        if (class_exists('\Swagger\Annotations\Swagger')) {
            $scanDir = $config['swagger_php']['scan'];
            $excludeDir = $config['swagger_php']['exclude'] ?? [];

            if (empty($scanDir)) {
                $scanDir = [sprintf('%s/src', $container->getParameter('kernel.project_dir'))];
            }

            $loaderDefinition = new Definition(SwaggerPhpConfigurationLoader::class);
            $loaderDefinition
                ->addArgument($scanDir)
                ->addArgument($excludeDir)
            ;

            return $loaderDefinition;
        }

        $pathToConfig = $config['configuration_file'];

        if (empty($pathToConfig)) {
            $pathToConfig = sprintf('%s/web/swagger.json', $container->getParameter('kernel.project_dir'));
        }

        $explodedPath = explode('.', $pathToConfig);
        $extension = end($explodedPath);

        if ('json' === $extension) {
            $configurationLoaderDefinitionId = JsonConfigurationLoader::class;
        } elseif ('yaml' === $extension || 'yml' === $extension) {
            $configurationLoaderDefinitionId = YamlConfigurationLoader::class;
        } else {
            throw new InvalidTypeException('Received unsupported file');
        }

        $loaderDefinition = new Definition($configurationLoaderDefinitionId);
        $loaderDefinition->addArgument($pathToConfig);

        return $loaderDefinition;
    }
}
