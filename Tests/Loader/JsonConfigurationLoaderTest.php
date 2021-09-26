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
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
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
class JsonConfigurationLoaderTest extends TestCase
{
    private const PATH_TO_CONFIG = __DIR__ . '/../Fixtures/Json/customer.json';
    private const MAP_ROUTE = [
        '/customers' => [
            'get' => 'customers_get',
            'post' => 'customers_post',
        ],
        '/customers/{userId}' => [
            'get' => 'customers_get_one',
            'put' => 'customers_update',
            'patch' => 'customers_patch',
            'delete' => 'customers_delete',
        ],
        '/customers/{userId}/password' => [
            'post' => 'customers_password_create',
            'put' => 'customers_password_update',
        ],
    ];

    /**
     * @var JsonConfigurationLoader
     */
    private $sut;

    /**
     * @var Router
     */
    private $router;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $this->router = new Router(new YamlFileLoader(new FileLocator(__DIR__ . '/../Fixtures')), 'routing.yaml');

        $this->sut = new JsonConfigurationLoader($parameterMerger, $this->router, self::PATH_TO_CONFIG);
    }

    public function testCanLoad(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();
        $expectedFileResource = new FileResource(self::PATH_TO_CONFIG);

        $definitionCollection = $this->sut->getSchemaDefinitionCollection();
        self::assertSame($expectedDefinitions->getIterator()->count(), $definitionCollection->getIterator()->count());

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $loadedSchema = $definitionCollection->getSchema($name);
            self::assertSame($expectedSchema->toArray(), $loadedSchema->toArray());

            $loadedResources = $definitionCollection->getSchemaResources($name);
            self::assertCount(1, $loadedResources);

            $loadedResource = $loadedResources[0];
            self::assertSame($expectedFileResource->getResource(), $loadedResource->getResource());
        }

        $expectedOperationsCount = 0;
        $operationCollection = $this->sut->getSchemaOperationCollection();

        /**
         * @var string $path
         * @var Path $pathObject
         */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                $expectedOperationsCount++;

                $operationCollection->getSchema(self::MAP_ROUTE[$path][$method], $method);

                $loadedResources = $definitionCollection->getSchemaResources($name);
                self::assertCount(1, $loadedResources);

                $loadedResource = $loadedResources[0];
                self::assertSame($expectedFileResource->getResource(), $loadedResource->getResource());
            }
        }

        self::assertCount($expectedOperationsCount, $operationCollection->getIterator());
    }
}
