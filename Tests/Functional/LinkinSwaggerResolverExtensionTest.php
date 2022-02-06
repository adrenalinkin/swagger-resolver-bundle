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
}
