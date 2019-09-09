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
     * @param string $routeName
     * @param string $method
     * @param Schema $schema
     *
     * @return self
     */
    public function addSchema(string $routeName, string $method, Schema $schema): self
    {
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
     * @throws PathNotFoundException
     */
    public function getSchema(string $routeName, string $method): Schema
    {
        if (empty($this->schemaCollection[$routeName])) {
            throw new PathNotFoundException($routeName);
        }

        if (empty($this->schemaCollection[$routeName][$method])) {
            throw new OperationNotFoundException($routeName, $method);
        }

        return $this->schemaCollection[$routeName][$method];
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
}
