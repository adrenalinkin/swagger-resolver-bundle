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

use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\ConfigurationTestCase;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class JsonConfigurationLoaderTest extends ConfigurationTestCase
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

    protected function getExpectedFileResourcesByRouteName(string $routerName): array
    {
        return [(new FileResource(FixturesProvider::PATH_TO_SWG_JSON))->getResource()];
    }

    protected function getExpectedFileResourceByDefinition(string $definitionName): string
    {
        return (new FileResource(FixturesProvider::PATH_TO_SWG_JSON))->getResource();
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
        $this->assertLoadSchemaDefinitionCollection($this->sut->getSchemaDefinitionCollection());
    }

    public function testCanLoadOperationCollection(): void
    {
        $this->assertLoadSchemaOperationCollection($this->sut->getSchemaOperationCollection());
    }
}
