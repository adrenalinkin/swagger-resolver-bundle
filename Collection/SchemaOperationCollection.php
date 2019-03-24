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
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
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

    /**
     * @param string $path
     * @param string $method
     * @param Schema $schema
     *
     * @return self
     */
    public function addSchema(string $path, string $method, Schema $schema): self
    {
        $this->schemaCollection[$path][$method] = $schema;

        return $this;
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return Schema
     *
     * @throws OperationNotFoundException
     * @throws PathNotFoundException
     */
    public function getSchema(string $path, string $method): Schema
    {
        if (empty($this->schemaCollection[$path])) {
            throw new PathNotFoundException($path);
        }

        if (empty($this->schemaCollection[$path][$method])) {
            throw new OperationNotFoundException($path, $method);
        }

        return $this->schemaCollection[$path][$method];
    }

    /**
     * @param string $path
     * @param FileResource $resource
     *
     * @return self
     */
    public function addSchemaResource(string $path, FileResource $resource): self
    {
        $this->resourceCollection[$path][] = $resource;

        return $this;
    }

    /**
     * @param string $path
     *
     * @return FileResource[]
     */
    public function getSchemaResources(string $path): array
    {
        return $this->resourceCollection[$path] ?? [];
    }
}
