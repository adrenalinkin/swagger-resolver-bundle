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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Loader;

use Closure;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\ConfigurationTestCase;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Nelmio\ApiDocBundle\ApiDocGenerator;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoaderTest extends ConfigurationTestCase
{
    /**
     * @var NelmioApiDocConfigurationLoader
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();
        $apiDocGenerator = $this->createApiDocGenerator();

        $this->sut = new NelmioApiDocConfigurationLoader($parameterMerger, $router, $apiDocGenerator, __DIR__.'/..');
    }

    protected function getExpectedFileResourcesByRouteName(string $routerName = ''): array
    {
        return FixturesProvider::getResourceByRouteName($routerName);
    }

    protected function getExpectedFileResourceByDefinition(string $definitionName): string
    {
        return FixturesProvider::getResourceByDefinition($definitionName);
    }

    public function testFailWhenRouteNotFound(): void
    {
        $this->expectException(OperationNotFoundException::class);

        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();
        $router->getRouteCollection()->remove('customers_get');
        $apiDocGenerator = $this->createApiDocGenerator();

        $sut = new NelmioApiDocConfigurationLoader($parameterMerger, $router, $apiDocGenerator, __DIR__.'/..');
        $sut->getSchemaDefinitionCollection();
    }

    public function testCanLoadDefinitionCollection(): void
    {
        $this->assertLoadSchemaDefinitionCollection($this->sut->getSchemaDefinitionCollection());
    }

    public function testCanLoadOperationCollection(): void
    {
        $this->assertLoadSchemaOperationCollection($this->sut->getSchemaOperationCollection());
    }

    private function createApiDocGenerator(): ApiDocGenerator
    {
        $apiDocGenerator = new ApiDocGenerator([], []);
        $setSwagger = Closure::bind(function (Swagger $swagger) {
            $this->swagger = $swagger;
        }, $apiDocGenerator, ApiDocGenerator::class);

        $setSwagger(FixturesProvider::loadFromJson());

        return $apiDocGenerator;
    }
}
