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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\DependencyInjection;

use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerNormalizerCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerValidatorCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\LinkinSwaggerResolverExtension;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\SwaggerNormalizerInterface;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use Nelmio\ApiDocBundle\DependencyInjection\NelmioApiDocExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinSwaggerResolverExtensionTest extends TestCase
{
    /**
     * @var LinkinSwaggerResolverExtension
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new LinkinSwaggerResolverExtension();
    }

    public function testCanLoadExtensionWithDefaultConfiguration(): void
    {
        $containerBuilder = $this->createContainerBuilder();

        $this->sut->load([[]], $containerBuilder);

        $containerBuilder->compile();

        self::assertSame(
            ['query', 'path', 'header'],
            $containerBuilder->getParameter('linkin_swagger_resolver.enable_normalization')
        );

        self::assertSame(
            StrictMergeStrategy::class,
            (string) $containerBuilder->getAlias(MergeStrategyInterface::class)
        );

        self::assertSame(
            SwaggerPhpConfigurationLoader::class,
            (string) $containerBuilder->getAlias(SwaggerConfigurationLoaderInterface::class)
        );

        $autoconfiguration = $containerBuilder->getAutoconfiguredInstanceof();

        self::assertArrayHasKey(SwaggerValidatorInterface::class, $autoconfiguration);
        self::assertTrue(
            $autoconfiguration[SwaggerValidatorInterface::class]->hasTag(SwaggerValidatorCompilerPass::TAG)
        );

        self::assertArrayHasKey(SwaggerNormalizerInterface::class, $autoconfiguration);
        self::assertTrue(
            $autoconfiguration[SwaggerNormalizerInterface::class]->hasTag(SwaggerNormalizerCompilerPass::TAG)
        );
    }

    public function testCanApplyCustomConfigurationLoadService(): void
    {
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->setDefinition(JsonConfigurationLoader::class, new Definition());

        $this->sut->load([[
            'configuration_loader_service' => JsonConfigurationLoader::class,
        ]], $containerBuilder);

        $containerBuilder->compile();

        self::assertSame(
            JsonConfigurationLoader::class,
            (string) $containerBuilder->getAlias(SwaggerConfigurationLoaderInterface::class)
        );
    }

    public function testCanApplyNelmioApiDocConfigurationLoadServiceWhenBundleAvailable(): void
    {
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->setParameter('kernel.bundles', ['NelmioApiDocBundle' => true]);
        $containerBuilder->setDefinition('nelmio_api_doc.generator.', (new Definition())->setSynthetic(true));

        $this->sut->load([[]], $containerBuilder);

        $containerBuilder->compile();

        self::assertSame(
            NelmioApiDocConfigurationLoader::class,
            (string) $containerBuilder->getAlias(SwaggerConfigurationLoaderInterface::class)
        );
    }

    public function testCanApplyJson(): void
    {
        $projectDir = sys_get_temp_dir();
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectDir);
        file_put_contents($projectDir.'/composer.lock', json_encode(['packages' => [], 'packages-dev' => []]));

        $this->sut->load([[
            'configuration_file' => FixturesProvider::PATH_TO_SWG_JSON,
        ]], $containerBuilder);

        $containerBuilder->compile();

        self::assertSame(
            JsonConfigurationLoader::class,
            (string) $containerBuilder->getAlias(SwaggerConfigurationLoaderInterface::class)
        );
    }

    public function testCanApplyYaml(): void
    {
        $projectDir = sys_get_temp_dir();
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectDir);
        file_put_contents($projectDir.'/composer.lock', json_encode(['packages' => [], 'packages-dev' => []]));

        $this->sut->load([[
            'configuration_file' => FixturesProvider::PATH_TO_SWG_YAML,
        ]], $containerBuilder);

        $containerBuilder->compile();

        self::assertSame(
            YamlConfigurationLoader::class,
            (string) $containerBuilder->getAlias(SwaggerConfigurationLoaderInterface::class)
        );
    }

    public function testFailWhenReceivedUnsupportedConfigurationFile(): void
    {
        $projectDir = sys_get_temp_dir();
        $containerBuilder = $this->createContainerBuilder();
        $containerBuilder->setParameter('kernel.project_dir', $projectDir);
        file_put_contents($projectDir.'/composer.lock', json_encode(['packages' => [], 'packages-dev' => []]));

        $this->expectException(InvalidTypeException::class);

        $this->sut->load([[
            'configuration_file' => __DIR__.'/../../composer.lock',
        ]], $containerBuilder);
    }

    public function testCanPrependNelmioApiDocConfiguration(): void
    {
        $containerBuilder = $this->createContainerBuilder();
        $extension = new NelmioApiDocExtension();
        $containerBuilder->registerExtension($extension);
        $this->sut->prepend($containerBuilder);

        $configs = $containerBuilder->getExtensionConfig($extension->getAlias());
        self::assertArrayHasKey('areas', reset($configs));
    }

    private function createContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.bundles', []);
        $containerBuilder->setParameter('kernel.project_dir', __DIR__.'/../../');
        $containerBuilder->setParameter('kernel.cache_dir', sys_get_temp_dir());
        $containerBuilder->setParameter('kernel.debug', true);

        $containerBuilder->setDefinition(RouterInterface::class, new Definition());

        return $containerBuilder;
    }
}
