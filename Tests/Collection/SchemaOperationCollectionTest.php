<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Collection;

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SchemaOperationCollectionTest extends TestCase
{
    /**
     * @var SchemaOperationCollection
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SchemaOperationCollection();
    }

    public function testRequestMethodWillBeInLowercase(): void
    {
        $routeNameFirst = 'first';
        $firstRouteWithPostMethod = FixturesProvider::createSchemaDefinition(['firstPost' => ['type' => 'int']]);
        $firstRouteWithDeleteMethod = FixturesProvider::createSchemaDefinition(['firstDelete' => ['type' => 'int']]);

        $this->sut->addSchema($routeNameFirst, 'PosT', $firstRouteWithPostMethod);
        $this->sut->addSchema($routeNameFirst, 'DeLeTe', $firstRouteWithDeleteMethod);

        $collected = [];
        foreach ($this->sut->getIterator() as $routeName => $methods) {
            foreach ($methods as $method => $schema) {
                $collected[(string) $routeName][$method] = $schema;
            }
        }

        self::assertSame($collected, [
            $routeNameFirst => [
                'post' => $firstRouteWithPostMethod,
                'delete' => $firstRouteWithDeleteMethod,
            ],
        ]);
    }

    public function testCanAddAndGetSchemaOperation(): void
    {
        $routeNameFirst = 'first';
        $firstRouteWithPostMethod = FixturesProvider::createSchemaDefinition(['firstPost' => ['type' => 'int']]);
        $firstRouteWithDeleteMethod = FixturesProvider::createSchemaDefinition(['firstDelete' => ['type' => 'int']]);

        $routeNameSecond = 'second';
        $secondRouteWithPostMethod = FixturesProvider::createSchemaDefinition(['secondPost' => ['type' => 'int']]);
        $secondRouteWithDeleteMethod = FixturesProvider::createSchemaDefinition(['secondDelete' => ['type' => 'int']]);

        $this->sut->addSchema($routeNameFirst, 'PosT', $firstRouteWithPostMethod);
        $this->sut->addSchema($routeNameFirst, 'DeLeTe', $firstRouteWithDeleteMethod);
        $this->sut->addSchema($routeNameSecond, 'post', $secondRouteWithPostMethod);
        $this->sut->addSchema($routeNameSecond, 'delete', $secondRouteWithDeleteMethod);

        self::assertSame($firstRouteWithPostMethod, $this->sut->getSchema($routeNameFirst, 'post'));
        self::assertSame($firstRouteWithDeleteMethod, $this->sut->getSchema($routeNameFirst, 'delete'));
        self::assertSame($secondRouteWithPostMethod, $this->sut->getSchema($routeNameSecond, 'post'));
        self::assertSame($secondRouteWithDeleteMethod, $this->sut->getSchema($routeNameSecond, 'delete'));
    }

    public function testCanThrowExceptionWhenTryToGetNotExistedSchemaOperation(): void
    {
        $routeNameFirst = 'first';
        $firstRouteWithPostMethod = FixturesProvider::createSchemaDefinition(['firstPost' => ['type' => 'int']]);

        $this->sut->addSchema($routeNameFirst, 'PosT', $firstRouteWithPostMethod);
        self::assertSame($firstRouteWithPostMethod, $this->sut->getSchema($routeNameFirst, 'post'));

        $this->expectException(OperationNotFoundException::class);
        $this->sut->getSchema('__UNDEFINED__', 'post');

        $this->expectException(OperationNotFoundException::class);
        $this->sut->getSchema($routeNameFirst, 'delete');
    }

    public function testSchemaOperationWillNotContainsDuplicatesAndApplyLastWinStrategy(): void
    {
        $routeName = 'first';
        $routeWithPostMethod = FixturesProvider::createSchemaDefinition(['firstPost' => ['type' => 'int']]);
        $routeWithPostMethodNew = FixturesProvider::createSchemaDefinition(['firstPostNew' => ['type' => 'int']]);

        $this->sut->addSchema($routeName, 'PosT', $routeWithPostMethod);
        self::assertSame($routeWithPostMethod, $this->sut->getSchema($routeName, 'post'));

        $this->sut->addSchema($routeName, 'post', $routeWithPostMethodNew);
        self::assertSame($routeWithPostMethodNew, $this->sut->getSchema($routeName, 'post'));
    }

    public function testCanGetEmptyArrayWhenResourcesNotFound(): void
    {
        self::assertSame([], $this->sut->getSchemaResources('__UNDEFINED__'));
    }

    public function testCanAddAndGetSchemaResources(): void
    {
        $routeNameFirst = 'first';
        $firstResource = new FileResource(FixturesProvider::getResourceByRouteName('customers_get')[0]);

        $routeNameSecond = 'second';
        $secondResource = new FileResource(FixturesProvider::getResourceByRouteName('customers_password_reset')[0]);

        $this->sut->addSchemaResource($routeNameFirst, $firstResource);
        $this->sut->addSchemaResource($routeNameSecond, $secondResource);

        self::assertSame([$firstResource], $this->sut->getSchemaResources($routeNameFirst));
        self::assertSame([$secondResource], $this->sut->getSchemaResources($routeNameSecond));
    }

    public function testSchemaResourcesWillNotContainsDuplicates(): void
    {
        $routeName = 'someName';
        $firstResource = new FileResource(FixturesProvider::getResourceByRouteName('customers_get')[0]);
        $secondResource = new FileResource(FixturesProvider::getResourceByRouteName('customers_password_reset')[0]);

        $this->sut->addSchemaResource($routeName, $firstResource);
        self::assertSame([$firstResource], $this->sut->getSchemaResources($routeName));

        $this->sut->addSchemaResource($routeName, $firstResource);
        self::assertSame([$firstResource], $this->sut->getSchemaResources($routeName));

        $this->sut->addSchemaResource($routeName, $secondResource);
        self::assertSame(
            [$firstResource, $secondResource],
            $this->sut->getSchemaResources($routeName)
        );
    }
}
