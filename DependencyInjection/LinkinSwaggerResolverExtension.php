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

use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerNormalizerCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerValidatorCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\SwaggerNormalizerInterface;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function class_exists;
use function end;
use function explode;
use function sprintf;

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
        $loader->load('services.yaml');

        $container->setParameter('linkin_swagger_resolver.enable_normalization', $config['enable_normalization']);

        $container->setAlias(MergeStrategyInterface::class, $config['path_merge_strategy']);

        $this->registerConfigurationLoader($container, $config);

        $container
            ->registerForAutoconfiguration(SwaggerValidatorInterface::class)
            ->addTag(SwaggerValidatorCompilerPass::TAG)
        ;

        $container
            ->registerForAutoconfiguration(SwaggerNormalizerInterface::class)
            ->addTag(SwaggerNormalizerCompilerPass::TAG)
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function registerConfigurationLoader(ContainerBuilder $container, array $config): void
    {
        if (!empty($config['configuration_loader_service'])) {
            $container->setAlias(SwaggerConfigurationLoaderInterface::class, $config['configuration_loader_service']);

            return;
        }

        $loaderDefinition = $this->getConfigurationLoaderDefinition($container, $config);
        $configurationLoaderDefinitionId = 'linkin_swagger_resolver.loader.configuration';

        $container->setDefinition($configurationLoaderDefinitionId, $loaderDefinition);
        $container->setAlias(SwaggerConfigurationLoaderInterface::class, $configurationLoaderDefinitionId);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     *
     * @return Definition
     */
    private function getConfigurationLoaderDefinition(ContainerBuilder $container, array $config): Definition
    {
        $loaderDefinition = new Definition();

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['NelmioApiDocBundle'])) {
            return $loaderDefinition
                ->setClass(NelmioApiDocConfigurationLoader::class)
                ->addArgument(new Reference('nelmio_api_doc.generator'))
            ;
        }

        if (class_exists('\Swagger\Annotations\Swagger')) {
            $scanDir = $config['swagger_php']['scan'];
            $excludeDir = $config['swagger_php']['exclude'] ?? [];

            if (empty($scanDir)) {
                $scanDir = [sprintf('%s/src', $container->getParameter('kernel.project_dir'))];
            }

            return $loaderDefinition
                ->setClass(SwaggerPhpConfigurationLoader::class)
                ->addArgument($scanDir)
                ->addArgument($excludeDir)
            ;
        }

        $pathToConfig = $config['configuration_file'];

        if (empty($pathToConfig)) {
            $pathToConfig = sprintf('%s/web/swagger.json', $container->getParameter('kernel.project_dir'));
        }

        $loaderDefinition->addArgument($pathToConfig);

        $explodedPath = explode('.', $pathToConfig);
        $extension = end($explodedPath);

        if ('json' === $extension) {
            return $loaderDefinition->setClass(JsonConfigurationLoader::class);
        }

        if ('yaml' === $extension || 'yml' === $extension) {
            return $loaderDefinition->setClass(YamlConfigurationLoader::class);
        }

        throw new InvalidTypeException('Received unsupported file');
    }
}
