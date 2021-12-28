<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Collection;

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SchemaDefinitionCollectionTest extends TestCase
{
    /**
     * @var SchemaDefinitionCollection
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SchemaDefinitionCollection();
    }

    public function testCanAddAndGetSchemaDefinition(): void
    {
        $definitionNameFirst = 'first';
        $definitionFirstObject = FixturesProvider::createSchemaDefinition(['first' => ['type' => 'boolean']]);

        $definitionNameSecond = 'second';
        $definitionSecondObject = FixturesProvider::createSchemaDefinition(['second' => ['type' => 'boolean']]);

        $this->sut->addSchema($definitionNameFirst, $definitionFirstObject);
        $this->sut->addSchema($definitionNameSecond, $definitionSecondObject);

        self::assertSame($definitionFirstObject, $this->sut->getSchema($definitionNameFirst));
        self::assertSame($definitionSecondObject, $this->sut->getSchema($definitionNameSecond));

        foreach ($this->sut->getIterator() as $definitionName => $definition) {
            self::assertContains($definitionName, [$definitionNameFirst, $definitionNameSecond]);
            self::assertContains($definition, [$definitionFirstObject, $definitionSecondObject]);
        }
    }

    public function testCanThrowExceptionWhenTryToGetNotExistedSchemaDefinition(): void
    {
        $definitionNameFirst = 'first';
        $definitionFirstObject = FixturesProvider::createSchemaDefinition(['first' => ['type' => 'boolean']]);

        $this->sut->addSchema($definitionNameFirst, $definitionFirstObject);
        self::assertSame($definitionFirstObject, $this->sut->getSchema($definitionNameFirst));

        $this->expectException(DefinitionNotFoundException::class);
        $this->sut->getSchema('__UNDEFINED__');
    }

    public function testSchemaDefinitionWillNotContainsDuplicatesAndApplyLastWinStrategy(): void
    {
        $definitionName = 'someName';
        $definitionFirstObject = FixturesProvider::createSchemaDefinition(['first' => ['type' => 'boolean']]);
        $definitionSecondObject = FixturesProvider::createSchemaDefinition(['second' => ['type' => 'boolean']]);

        $this->sut->addSchema($definitionName, $definitionFirstObject);
        self::assertSame($definitionFirstObject, $this->sut->getSchema($definitionName));

        $this->sut->addSchema($definitionName, $definitionSecondObject);
        self::assertSame($definitionSecondObject, $this->sut->getSchema($definitionName));
    }

    public function testCanGetEmptyArrayWhenResourcesNotFound(): void
    {
        self::assertSame([], $this->sut->getSchemaResources('__UNDEFINED__'));
    }

    public function testCanAddAndGetSchemaResources(): void
    {
        $definitionNameFirst = 'first';
        $customerFullResource = new FileResource(FixturesProvider::getResourceByDefinition('CustomerFull'));

        $definitionNameSecond = 'second';
        $customerNewResource = new FileResource(FixturesProvider::getResourceByDefinition('CustomerNew'));

        $this->sut->addSchemaResource($definitionNameFirst, $customerFullResource);
        $this->sut->addSchemaResource($definitionNameSecond, $customerNewResource);

        self::assertSame([$customerFullResource], $this->sut->getSchemaResources($definitionNameFirst));
        self::assertSame([$customerNewResource], $this->sut->getSchemaResources($definitionNameSecond));
    }

    public function testSchemaResourcesWillNotContainsDuplicates(): void
    {
        $definitionName = 'someName';
        $customerFullResource = new FileResource(FixturesProvider::getResourceByDefinition('CustomerFull'));
        $customerNewResource = new FileResource(FixturesProvider::getResourceByDefinition('CustomerNew'));

        $this->sut->addSchemaResource($definitionName, $customerFullResource);
        self::assertSame([$customerFullResource], $this->sut->getSchemaResources($definitionName));

        $this->sut->addSchemaResource($definitionName, $customerFullResource);
        self::assertSame([$customerFullResource], $this->sut->getSchemaResources($definitionName));

        $this->sut->addSchemaResource($definitionName, $customerNewResource);
        self::assertSame(
            [$customerFullResource, $customerNewResource],
            $this->sut->getSchemaResources($definitionName)
        );
    }
}
