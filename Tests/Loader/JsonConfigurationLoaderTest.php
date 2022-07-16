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

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class JsonConfigurationLoaderTest extends TestCase
{
    /**
     * @var JsonConfigurationLoader
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();

        $this->sut = new JsonConfigurationLoader($parameterMerger, $router, FixturesProvider::PATH_TO_SWG_JSON);
    }

    public function testFailWhenRouteNotFound(): void
    {
        $this->expectException(OperationNotFoundException::class);

        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();
        $router->getRouteCollection()->remove('customers_get');

        $sut = new JsonConfigurationLoader($parameterMerger, $router, FixturesProvider::PATH_TO_SWG_JSON);
        $sut->getSchemaDefinitionCollection();
    }

    public function testCanLoadDefinitionCollection(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();
        $expectedFileResource = new FileResource(FixturesProvider::PATH_TO_SWG_JSON);

        $definitionCollection = $this->sut->getSchemaDefinitionCollection();
        self::assertSame($expectedDefinitions->getIterator()->count(), $definitionCollection->getIterator()->count());

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $loadedDefinitionSchema = $definitionCollection->getSchema($name);
            self::assertSame($expectedSchema->toArray(), $loadedDefinitionSchema->toArray());

            $loadedResources = $definitionCollection->getSchemaResources($name);
            self::assertCount(1, $loadedResources);

            $loadedResource = $loadedResources[0];
            self::assertSame($expectedFileResource->getResource(), $loadedResource->getResource());
        }
    }

    public function testCanLoadOperationCollection(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedFileResource = new FileResource(FixturesProvider::PATH_TO_SWG_JSON);
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
                self::assertCount(1, $loadedResources);

                $loadedResource = $loadedResources[0];
                self::assertSame($expectedFileResource->getResource(), $loadedResource->getResource());

                ++$expectedOperationsCount;
            }
        }

        self::assertCount($expectedOperationsCount, $operationCollection->getIterator());
    }
}
