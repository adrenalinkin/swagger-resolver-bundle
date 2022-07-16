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
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoaderTest extends TestCase
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
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        $definitionCollection = $this->sut->getSchemaDefinitionCollection();
        self::assertSame($expectedDefinitions->getIterator()->count(), $definitionCollection->getIterator()->count());

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $loadedDefinitionSchema = $definitionCollection->getSchema($name);
            self::assertSame($expectedSchema->toArray(), $loadedDefinitionSchema->toArray());

            $loadedResources = $definitionCollection->getSchemaResources($name);
            self::assertCount(1, $loadedResources);

            $loadedResource = $loadedResources[0];
            self::assertSame(FixturesProvider::getResourceByDefinition($name), $loadedResource->getResource());
        }
    }

    public function testCanLoadOperationCollection(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $operationCollection = $this->sut->getSchemaOperationCollection();
        $expectedOperationsCount = 0;

        /** @var Path $pathObject */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routerName = FixturesProvider::getRouteName($path, $method);

                $pathDefinitionSchema = $operationCollection->getSchema($routerName, $method);

                /** @var Schema $definition */
                foreach ($pathDefinitionSchema->getProperties()->getIterator() as $name => $definition) {
                    if ($definition->getTitle() === 'body') {
                        /** Skip complicated check @see OperationParameterMergerTest */
                        continue;
                    }

                    $expectedName = $name.'/'.$definition->getTitle();
                    self::assertTrue($operation->getParameters()->has($expectedName), "Should contains $expectedName");
                }

                $loadedResources = $operationCollection->getSchemaResources($routerName);
                $expectedResources = FixturesProvider::getResourceByRouteName($routerName);

                foreach ($loadedResources as $loadedResource) {
                    self::assertContains($loadedResource->getResource(), $expectedResources);
                }

                ++$expectedOperationsCount;
            }
        }

        self::assertCount($expectedOperationsCount, $operationCollection->getIterator());
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
