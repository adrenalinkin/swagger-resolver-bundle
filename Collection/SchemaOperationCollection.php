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

use ArrayIterator;
use EXSyst\Component\Swagger\Schema;
use IteratorAggregate;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Symfony\Component\Config\Resource\FileResource;
use function strtolower;

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

    /**
     * @param string $routeName
     * @param string $method
     * @param Schema $schema
     *
     * @return self
     */
    public function addSchema(string $routeName, string $method, Schema $schema): self
    {
        $method = $this->normalizeMethod($method);
        $this->schemaCollection[$routeName][$method] = $schema;

        return $this;
    }

    /**
     * @param string $routeName
     * @param string $method
     *
     * @return Schema
     *
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

    /**
     * @param string $routeName
     * @param FileResource $resource
     *
     * @return self
     */
    public function addSchemaResource(string $routeName, FileResource $resource): self
    {
        $this->resourceCollection[$routeName][] = $resource;

        return $this;
    }

    /**
     * @param string $routeName
     *
     * @return FileResource[]
     */
    public function getSchemaResources(string $routeName): array
    {
        return $this->resourceCollection[$routeName] ?? [];
    }

    /**
     * @param string $method
     *
     * @return string
     */
    private function normalizeMethod(string $method): string
    {
        return strtolower($method);
    }
}
