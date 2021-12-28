<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\DependencyInjection;

use function array_merge_recursive;
use function class_exists;
use function end;
use function explode;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerNormalizerCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerValidatorCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\SwaggerNormalizerInterface;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use function md5;
use function sprintf;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Routing\RouterInterface;
use function time;
use function uniqid;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinSwaggerResolverExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var string
     */
    private $globalAreaName;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('linkin_swagger_resolver.enable_normalization', $config['enable_normalization']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

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
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        if (!$container->hasExtension('nelmio_api_doc')) {
            return;
        }

        $nelmioConfigs = $container->getExtensionConfig('nelmio_api_doc');
        $globalArea = ['path_patterns' => [], 'host_patterns' => []];

        foreach ($nelmioConfigs as $config) {
            if (empty($config['areas'])) {
                continue;
            }

            foreach ($config['areas'] as $area => $areaConfig) {
                $globalArea = array_merge_recursive($globalArea, $areaConfig);
            }
        }

        $globalArea['with_annotation'] = false;

        $this->globalAreaName = md5(uniqid((string) time(), true));

        $container->prependExtensionConfig('nelmio_api_doc', ['areas' => [$this->globalAreaName => $globalArea]]);
    }

    private function registerConfigurationLoader(ContainerBuilder $container, array $config): void
    {
        if (!empty($config['configuration_loader_service'])) {
            $container->setAlias(SwaggerConfigurationLoaderInterface::class, $config['configuration_loader_service']);

            return;
        }

        $loaderDefinition = $this->getConfigurationLoaderDefinition($container, $config);

        $container->setDefinition($loaderDefinition->getClass(), $loaderDefinition);
        $container->setAlias(SwaggerConfigurationLoaderInterface::class, $loaderDefinition->getClass());
    }

    private function getConfigurationLoaderDefinition(ContainerBuilder $container, array $config): Definition
    {
        $loaderDefinition = new Definition();
        $loaderDefinition
            ->addArgument(new Reference(OperationParameterMerger::class))
            ->addArgument(new Reference(RouterInterface::class))
        ;

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['NelmioApiDocBundle'])) {
            return $loaderDefinition
                ->setClass(NelmioApiDocConfigurationLoader::class)
                ->addArgument(new Reference(sprintf('nelmio_api_doc.generator.%s', $this->globalAreaName)))
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

        if ('yaml' === $extension || 'yml' === $extension) {
            return $loaderDefinition->setClass(YamlConfigurationLoader::class);
        }

        if ('json' === $extension) {
            return $loaderDefinition->setClass(JsonConfigurationLoader::class);
        }

        throw new InvalidTypeException('Received unsupported file');
    }
}
