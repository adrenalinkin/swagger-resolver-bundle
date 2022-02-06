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
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

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
            'options' => ['test_case' => 'NelmioApiDoc'],
            'expected' => NelmioApiDocConfigurationLoader::class
        ];
        yield [
            'options' => ['test_case' => 'SwaggerPhp'],
            'expected' => SwaggerPhpConfigurationLoader::class
        ];
        yield [
            'options' => ['test_case' => 'default', 'disable_swagger_php' => true],
            'expected' => JsonConfigurationLoader::class
        ];
    }

    /**
     * @dataProvider canApplyYamlLoaderDataProvider
     */
    public function testCanApplyYamlLoader(string $testCase): void
    {
        self::createClient([
            'test_case' => $testCase,
            'disable_swagger_php' => true,
        ]);

        self::assertTrue(self::getTestContainer()->has(YamlConfigurationLoader::class));
    }

    public function canApplyYamlLoaderDataProvider(): iterable
    {
        yield ['LoadFromYaml'];
        yield ['LoadFromYml'];
    }

    public function testFailWhenReceivedUnsupportedConfigurationFile(): void
    {
        $this->expectException(InvalidTypeException::class);

        self::createClient([
            'test_case' => 'LoadIncorrectFile',
            'disable_swagger_php' => true,
        ]);
    }

    public function canLoadFromExplicitlyDefinedLoader(): void
    {
        self::createClient([
            'test_case' => 'LoadFromExplicitlyDefinedLoader',
        ]);

        self::assertTrue(self::getTestContainer()->has(JsonConfigurationLoader::class));
    }
}
