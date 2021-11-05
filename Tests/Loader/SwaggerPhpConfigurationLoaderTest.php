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

use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerPhpConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerPhpConfigurationLoaderTest extends TestCase
{
    /**
     * @var SwaggerPhpConfigurationLoader
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = new Router(new YamlFileLoader(new FileLocator(__DIR__ . '/../Fixtures')), 'routing.yaml');

        $this->sut = new SwaggerPhpConfigurationLoader(
            $parameterMerger,
            $router,
            [__DIR__ . '/../Fixtures/SwaggerPhp'],
            []
        );
    }

    public function testFailWhenRouteNotFound(): void
    {
        $this->expectException(OperationNotFoundException::class);

        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = new Router(new YamlFileLoader(new FileLocator(__DIR__ . '/../Fixtures')), 'routing.yaml');
        $router->getRouteCollection()->remove('customers_get');

        $sut = new SwaggerPhpConfigurationLoader(
            $parameterMerger,
            $router,
            [__DIR__ . '/../Fixtures/SwaggerPhp'],
            []
        );
        $sut->getSchemaDefinitionCollection();
    }

    public function testCanLoadDefinitionCollection(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        $definitionCollection = $this->sut->getSchemaDefinitionCollection();
        self::assertSame($expectedDefinitions->getIterator()->count(), $definitionCollection->getIterator()->count());

        /**
         * @var string $path
         * @var Schema $expectedSchema
         */
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

        /**
         * @var string $path
         * @var Path $pathObject
         */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routerName = FixturesProvider::getRouteName($path, $method);

                $operationCollection->getSchema($routerName, $method);

                $loadedResources = $operationCollection->getSchemaResources($routerName);
                $expectedResources = FixturesProvider::getResourceByRouteName($routerName);

                foreach ($loadedResources as $loadedResource) {
                    self::assertContains($loadedResource->getResource(), $expectedResources);
                }

                $expectedOperationsCount++;
            }
        }

        self::assertCount($expectedOperationsCount, $operationCollection->getIterator());
    }
}
