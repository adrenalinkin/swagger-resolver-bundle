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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterLocationEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class ConfigurationTestCase extends TestCase
{
    abstract protected function getExpectedFileResourcesByRouteName(string $routerName): array;

    abstract protected function getExpectedFileResourceByDefinition(string $definitionName): string;

    protected function assertLoadSchemaDefinitionCollection(SchemaDefinitionCollection $definitionCollection): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        self::assertSame($expectedDefinitions->getIterator()->count(), $definitionCollection->getIterator()->count());

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $loadedDefinitionSchema = $definitionCollection->getSchema($name);
            self::assertSame($expectedSchema->toArray(), $loadedDefinitionSchema->toArray());

            $loadedResources = $definitionCollection->getSchemaResources($name);
            self::assertCount(1, $loadedResources);

            $loadedResource = $loadedResources[0];
            self::assertSame($this->getExpectedFileResourceByDefinition($name), $loadedResource->getResource());
        }
    }

    protected function assertLoadSchemaOperationCollection(SchemaOperationCollection $operationCollection): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedOperationsCount = 0;

        /** @var Path $pathObject */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routerName = FixturesProvider::getRouteName($path, $method);

                self::assertOperationSchema($operationCollection->getSchema($routerName, $method), $operation);

                $loadedResources = $operationCollection->getSchemaResources($routerName);
                $expectedResources = $this->getExpectedFileResourcesByRouteName($routerName);

                foreach ($loadedResources as $loadedResource) {
                    self::assertContains($loadedResource->getResource(), $expectedResources);
                }

                ++$expectedOperationsCount;
            }
        }

        self::assertCount($expectedOperationsCount, $operationCollection->getIterator());
    }

    public static function assertOperationSchema(Schema $operationCollection, Operation $operation): void
    {
        /** @var Schema $definition */
        foreach ($operationCollection->getProperties()->getIterator() as $name => $definition) {
            if (ParameterLocationEnum::IN_BODY === $definition->getTitle()) {
                /* @see OperationParameterMergerTest Skip complicated check */
                continue;
            }

            $expectedName = $name.'/'.$definition->getTitle();
            self::assertTrue($operation->getParameters()->has($expectedName), "Should contains $expectedName");
        }
    }
}
