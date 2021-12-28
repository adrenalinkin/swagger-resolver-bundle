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

namespace Linkin\Bundle\SwaggerResolverBundle\Collection;

use function array_values;
use ArrayIterator;
use EXSyst\Component\Swagger\Schema;
use IteratorAggregate;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use function strtolower;
use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SchemaOperationCollection implements IteratorAggregate
{
    /**
     * @var Schema[][]
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

    public function addSchema(string $routeName, string $method, Schema $schema): self
    {
        $method = $this->normalizeMethod($method);
        $this->schemaCollection[$routeName][$method] = $schema;

        return $this;
    }

    /**
     * @throws OperationNotFoundException
     */
    public function getSchema(string $routeName, string $method): Schema
    {
        $method = $this->normalizeMethod($method);
        $schema = $this->schemaCollection[$routeName][$method] ?? null;

        if (!$schema) {
            throw new OperationNotFoundException($routeName, $method);
        }

        return $schema;
    }

    public function addSchemaResource(string $routeName, FileResource $resource): self
    {
        $this->resourceCollection[$routeName][(string) $resource->getResource()] = $resource;

        return $this;
    }

    /**
     * @return FileResource[]
     */
    public function getSchemaResources(string $routeName): array
    {
        return array_values($this->resourceCollection[$routeName] ?? []);
    }

    private function normalizeMethod(string $method): string
    {
        return strtolower($method);
    }
}
