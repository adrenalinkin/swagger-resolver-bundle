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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Configuration;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerCachedConfiguration;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfiguration;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\ConfigurationTestCase;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerCachedConfigurationTest extends TestCase
{
    private const PATH_TO_CACHE = FixturesProvider::PATH_TO_VAR_DIR.'/linkin_swagger_resolver';

    /**
     * @var SwaggerConfiguration
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();

        $loader = new YamlConfigurationLoader($parameterMerger, $router, FixturesProvider::PATH_TO_SWG_YAML);
        $this->sut = new SwaggerCachedConfiguration($loader, FixturesProvider::PATH_TO_VAR_DIR, false);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(self::PATH_TO_CACHE);
    }

    public function testCanGetDefinition(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $cacheFile = self::PATH_TO_CACHE.'/definitions/'.$name.'_'.md5($name).'.php';

            if (method_exists($this, 'assertFileDoesNotExist')) {
                self::assertFileDoesNotExist($cacheFile);
            } else {
                self::assertFileNotExists($cacheFile);
            }

            $loadedDefinitionSchema = $this->sut->getDefinition($name);
            self::assertFileExists($cacheFile);

            self::assertSame($expectedSchema->toArray(), $loadedDefinitionSchema->toArray());
        }
    }

    public function testCanGetPathDefinition(): void
    {
        $swagger = FixturesProvider::loadFromJson();

        /** @var Path $pathObject */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routeName = FixturesProvider::getRouteName($path, $method);
                $cacheFile = self::PATH_TO_CACHE.'/paths/'.$routeName.'/'.$method.'_'.md5($routeName.$method).'.php';

                if (method_exists($this, 'assertFileDoesNotExist')) {
                    self::assertFileDoesNotExist($cacheFile);
                } else {
                    self::assertFileNotExists($cacheFile);
                }

                $pathDefinitionSchema = $this->sut->getPathDefinition($routeName, $method);
                self::assertFileExists($cacheFile);

                ConfigurationTestCase::assertOperationSchema($pathDefinitionSchema, $operation);
            }
        }
    }

    public function testCanWarmUp(): void
    {
        if (method_exists($this, 'assertDirectoryDoesNotExist')) {
            self::assertDirectoryDoesNotExist(self::PATH_TO_CACHE);
        } else {
            self::assertDirectoryNotExists(self::PATH_TO_CACHE);
        }

        ob_start();
        $this->sut->warmUp('any');
        $output = ob_get_clean();

        self::assertEmpty($output);
        self::assertDirectoryExists(self::PATH_TO_CACHE);

        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            self::assertFileExists(self::PATH_TO_CACHE.'/definitions/'.$name.'_'.md5($name).'.php');
        }

        /** @var Path $pathObject */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $routeName = FixturesProvider::getRouteName($path, $method);

                self::assertFileExists(
                    self::PATH_TO_CACHE.'/paths/'.$routeName.'/'.$method.'_'.md5($routeName.$method).'.php'
                );
            }
        }
    }

    public function testCanShowMessageWhenSourceFileNotFoundForDefinition(): void
    {
        $definitionName = 'NOT-FOUND-DEFINITION';
        $definitionCollection = new SchemaDefinitionCollection();
        $definitionCollection->addSchema(
            $definitionName,
            FixturesProvider::createSchemaDefinition(['one' => ['type' => 'boolean']])
        );

        $schemaName = 'NOT-FOUND-SCHEMA';
        $schemaCollection = new SchemaOperationCollection();
        $schemaCollection->addSchema(
            $schemaName,
            'PUT',
            FixturesProvider::createSchemaDefinition(['one' => ['type' => 'boolean']])
        );

        $loaderMock = $this->createMock(SwaggerConfigurationLoaderInterface::class);
        $loaderMock->method('getSchemaDefinitionCollection')->willReturn($definitionCollection);
        $loaderMock->method('getSchemaOperationCollection')->willReturn($schemaCollection);

        $sut = new SwaggerCachedConfiguration($loaderMock, FixturesProvider::PATH_TO_VAR_DIR, false);

        ob_start();
        $sut->warmUp('any');
        $output = ob_get_clean();

        if (method_exists($this, 'assertMatchesRegularExpression')) {
            self::assertMatchesRegularExpression("/.*{$definitionName}.*/", $output);
            self::assertMatchesRegularExpression("/.*{$schemaName}.*/", $output);
        } else {
            self::assertRegExp("/.*{$definitionName}.*/", $output);
            self::assertRegExp("/.*{$schemaName}.*/", $output);
        }
    }
}
