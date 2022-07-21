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
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfiguration;
use Linkin\Bundle\SwaggerResolverBundle\Loader\YamlConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\ConfigurationTestCase;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerConfigurationTest extends TestCase
{
    /**
     * @var SwaggerConfiguration
     */
    private $sut;

    protected function setUp(): void
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter();

        $loader = new YamlConfigurationLoader($parameterMerger, $router, FixturesProvider::PATH_TO_SWG_YAML);
        $this->sut = new SwaggerConfiguration($loader);
    }

    public function testCanGetDefinition(): void
    {
        $swagger = FixturesProvider::loadFromJson();
        $expectedDefinitions = $swagger->getDefinitions();

        /** @var Schema $expectedSchema */
        foreach ($expectedDefinitions->getIterator() as $name => $expectedSchema) {
            $loadedDefinitionSchema = $this->sut->getDefinition($name);
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
                $routerName = FixturesProvider::getRouteName($path, $method);

                $pathDefinitionSchema = $this->sut->getPathDefinition($routerName, $method);
                ConfigurationTestCase::assertOperationSchema($pathDefinitionSchema, $operation);
            }
        }
    }
}
