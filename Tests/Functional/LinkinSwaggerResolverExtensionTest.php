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
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\FileAppKernel;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\NelmioAppKernel;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\SwaggerPhpAppKernel;
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
            'options' => ['kernelClass' => NelmioAppKernel::class],
            'expected' => NelmioApiDocConfigurationLoader::class,
        ];
        yield [
            'options' => ['kernelClass' => SwaggerPhpAppKernel::class],
            'expected' => SwaggerPhpConfigurationLoader::class,
        ];
        yield [
            'options' => ['kernelClass' => FileAppKernel::class],
            'expected' => JsonConfigurationLoader::class,
        ];
    }

    /**
     * @dataProvider canApplyYamlLoaderDataProvider
     */
    public function testCanApplyYamlLoader(string $pathToFile): void
    {
        self::createClient([
            'config' => ['linkin_swagger_resolver' => ['configuration_file' => $pathToFile]],
            'kernelClass' => FileAppKernel::class,
        ]);

        self::assertTrue(self::getTestContainer()->has(YamlConfigurationLoader::class));
    }

    public function canApplyYamlLoaderDataProvider(): iterable
    {
        yield ['%kernel.project_dir%/web/swagger.yaml'];
        yield ['%kernel.project_dir%/web/swagger.yaml'];
    }

    public function testFailWhenReceivedUnsupportedConfigurationFile(): void
    {
        $this->expectException(InvalidTypeException::class);

        self::createClient([
            'config' => ['linkin_swagger_resolver' => ['configuration_file' => '%kernel.project_dir%/src/swagger.php']],
            'kernelClass' => FileAppKernel::class,
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
            'config' => [
                'linkin_swagger_resolver' => [
                    'configuration_loader_service' => JsonConfigurationLoader::class,
                ],
            ],
            'serviceClosure' => $closure,
        ]);

        self::assertTrue(self::getTestContainer()->has(JsonConfigurationLoader::class));
    }
}
