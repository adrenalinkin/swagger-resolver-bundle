<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Collection;

use ArrayIterator;
use EXSyst\Component\Swagger\Schema;
use IteratorAggregate;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SchemaDefinitionCollection implements IteratorAggregate
{
    /**
     * @var Schema[]
     */
    private $schemaCollection = [];

    /**
     * @var FileResource[][]
     */
    private $resourceCollection = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->schemaCollection);
    }

    /**
     * @param string $definitionName
     * @param Schema $schema
     *
     * @return self
     */
    public function addSchema(string $definitionName, Schema $schema): self
    {
        $this->schemaCollection[$definitionName] = $schema;

        return $this;
    }

    /**
     * @param string $definitionName
     *
     * @return Schema
     *
     * @throws DefinitionNotFoundException
     */
    public function getSchema(string $definitionName): Schema
    {
        if (empty($this->schemaCollection[$definitionName])) {
            throw new DefinitionNotFoundException($definitionName);
        }

        return $this->schemaCollection[$definitionName];
    }

    /**
     * @param string $definitionName
     * @param FileResource $resource
     *
     * @return self
     */
    public function addSchemaResource(string $definitionName, FileResource $resource): self
    {
        $this->resourceCollection[$definitionName][] = $resource;

        return $this;
    }

    /**
     * @param string $definitionName
     *
     * @return FileResource[]
     */
    public function getSchemaResources(string $definitionName): array
    {
        return $this->resourceCollection[$definitionName] ?? [];
    }
}
