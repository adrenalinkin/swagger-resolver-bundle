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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional;

use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\TestAppKernel;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class LinkinSwaggerResolverExtensionTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canApplyDefaultFallbackDataProvider
     */
    public function testCanApplyDefaultFallback(array $clientOptions, string $expectedLoader): void
    {
        self::createClient($clientOptions);

        self::assertTrue(self::getTestContainer()->has($expectedLoader));
    }

    public function canApplyDefaultFallbackDataProvider(): iterable
    {
        yield [
            'options' => ['test_case' => TestAppKernel::LOADER_NELMIO_API_DOC],
            'expected' => NelmioApiDocConfigurationLoader::class,
        ];
        yield [
            'options' => ['test_case' => TestAppKernel::LOADER_SWAGGER_PHP],
            'expected' => SwaggerPhpConfigurationLoader::class,
        ];
        yield [
            'options' => ['test_case' => 'default', 'disable_swagger_php' => true],
            'expected' => JsonConfigurationLoader::class,
        ];
    }

    /**
     * @dataProvider canApplyYamlLoaderDataProvider
     */
    public function testCanApplyYamlLoader(string $testCase, array $config): void
    {
        self::createClient([
            'test_case' => $testCase,
            'config' => $config,
            'disable_swagger_php' => true,
        ]);

        self::assertTrue(self::getTestContainer()->has(YamlConfigurationLoader::class));
    }

    public function canApplyYamlLoaderDataProvider(): iterable
    {
        yield ['LoadFromYaml', ['configuration_file' => '%kernel.project_dir%/web/swagger.yaml']];
        yield ['LoadFromYml', ['configuration_file' => '%kernel.project_dir%/web/swagger.yaml']];
    }

    public function testFailWhenReceivedUnsupportedConfigurationFile(): void
    {
        $this->expectException(InvalidTypeException::class);

        self::createClient([
            'test_case' => 'default',
            'config' => ['configuration_file' => '%kernel.project_dir%/src/swagger.php'],
            'disable_swagger_php' => true,
        ]);
    }

    public function testCanLoadFromExplicitlyDefinedLoader(): void
    {
        $closure = static function (ContainerBuilder $containerBuilder) {
            $containerBuilder->register(JsonConfigurationLoader::class, JsonConfigurationLoader::class)
                ->addArgument(new Reference(OperationParameterMerger::class))
                ->addArgument(new Reference('router.default'))
                ->addArgument('%kernel.project_dir%/web/swagger.json')
            ;
        };

        self::createClient([
            'test_case' => 'LoadFromExplicitlyDefinedLoader',
            'config' => ['configuration_loader_service' => JsonConfigurationLoader::class],
            'serviceClosure' => $closure,
        ]);

        self::assertTrue(self::getTestContainer()->has(JsonConfigurationLoader::class));
    }
}
