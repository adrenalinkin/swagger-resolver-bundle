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
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class YamlConfigurationLoaderTest extends TestCase
{
    private const PATH_TO_CONFIG = __DIR__.'/../Fixtures/Yaml/customer.yaml';

    /**
     * @var YamlConfigurationLoader
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = new Router(new YamlFileLoader(new FileLocator(__DIR__.'/../Fixtures')), 'routing.yaml');

        $this->sut = new YamlConfigurationLoader($parameterMerger, $router, self::PATH_TO_CONFIG);
    }

    public function testCanLoadDefinitionCollection(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();
        $expectedFileResource = new FileResource(self::PATH_TO_CONFIG);

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
        $expectedFileResource = new FileResource(self::PATH_TO_CONFIG);
        $operationCollection = $this->sut->getSchemaOperationCollection();
        $expectedOperationsCount = 0;

        /**
         * @var string $path
         * @var Path   $pathObject
         */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routerName = FixturesProvider::getRouteName($path, $method);

                $operationCollection->getSchema($routerName, $method);

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
