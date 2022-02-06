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
    public function testCanApplyNelmioApiDocByDefault(): void
    {
        self::createClient(['test_case' => 'NelmioApiDoc']);

        self::assertTrue(self::getTestContainer()->has(NelmioApiDocConfigurationLoader::class));
    }

    public function testCanApplyFallbackToSwaggerPhp(): void
    {
        self::createClient(['test_case' => 'SwaggerPhp']);

        self::assertTrue(self::getTestContainer()->has(SwaggerPhpConfigurationLoader::class));
    }

    public function testCanApplyFallbackToJsonFile(): void
    {
        self::createClient(['test_case' => 'Json', 'disable_swagger_php' => true]);

        self::assertTrue(self::getTestContainer()->has(JsonConfigurationLoader::class));
    }
}
