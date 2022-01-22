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

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use ReflectionClass;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractSwaggerConfigurationLoader implements SwaggerConfigurationLoaderInterface
{
    /**
     * @var SchemaDefinitionCollection
     */
    private $definitionCollection;

    /**
     * @var SchemaOperationCollection
     */
    private $operationCollection;

    /**
     * @var OperationParameterMerger
     */
    private $parameterMerger;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string[][]
     */
    private $mapPathToRouteName = [];

    /**
     * @var FileResource[]
     */
    private $mapRouteNameToSourceFile = [];

    public function __construct(OperationParameterMerger $parameterMerger, RouterInterface $router)
    {
        $this->parameterMerger = $parameterMerger;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaDefinitionCollection(): SchemaDefinitionCollection
    {
        if (!$this->definitionCollection) {
            $this->registerCollections();
        }

        return $this->definitionCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaOperationCollection(): SchemaOperationCollection
    {
        if (!$this->operationCollection) {
            $this->registerCollections();
        }

        return $this->operationCollection;
    }

    /**
     * Load full configuration and returns Swagger object.
     */
    abstract protected function loadConfiguration(): Swagger;

    /**
     * Add file resources for swagger definitions.
     */
    abstract protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void;

    /**
     * Add file resources for swagger operations.
     */
    protected function registerOperationResources(SchemaOperationCollection $operationCollection): void
    {
        foreach ($operationCollection->getIterator() as $routeName => $methodList) {
            $operationCollection->addSchemaResource($routeName, $this->mapRouteNameToSourceFile[$routeName]);
        }
    }

    private function normalizeMethod(string $method): string
    {
        return strtolower($method);
    }

    /**
     * Register collection according to loaded Swagger object.
     */
    private function registerCollections(): void
    {
        $this->initRouteMaps();
        $swaggerConfiguration = $this->loadConfiguration();

        $this->definitionCollection = new SchemaDefinitionCollection();
        $this->operationCollection = new SchemaOperationCollection();

        foreach ($swaggerConfiguration->getDefinitions()->getIterator() as $definitionName => $definition) {
            $this->definitionCollection->addSchema($definitionName, $definition);
        }

        $this->registerDefinitionResources($this->definitionCollection);

        /** @var Path $pathObject */
        foreach ($swaggerConfiguration->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $method = $this->normalizeMethod($method);
                $routeName = $this->mapPathToRouteName[$path][$method] ?? null;

                if (null === $routeName) {
                    throw new OperationNotFoundException($path, $method);
                }

                $schema = $this->parameterMerger->merge($operation, $swaggerConfiguration->getDefinitions());

                $this->operationCollection->addSchema($routeName, $method, $schema);

                /** @var Parameter $parameter */
                foreach ($operation->getParameters()->getIterator() as $parameter) {
                    $ref = $parameter->getSchema()->getRef();

                    if (!\is_string($ref)) {
                        continue;
                    }

                    $explodedName = explode('/', $ref);
                    $definitionName = end($explodedName);

                    foreach ($this->definitionCollection->getSchemaResources($definitionName) as $fileResource) {
                        $this->operationCollection->addSchemaResource($routeName, $fileResource);
                    }
                }
            }
        }

        $this->registerOperationResources($this->operationCollection);
    }

    private function initRouteMaps(): void
    {
        $this->mapPathToRouteName = [];
        $this->mapRouteNameToSourceFile = [];

        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            foreach ($route->getMethods() as $method) {
                $defaults = $route->getDefaults();
                $exploded = explode('::', $defaults['_controller']);
                $controllerName = reset($exploded);
                $fullClassName = (new ReflectionClass($controllerName))->getFileName();

                $this->mapPathToRouteName[$route->getPath()][$this->normalizeMethod($method)] = $routeName;
                $this->mapRouteNameToSourceFile[$routeName] = new FileResource($fullClassName);
            }
        }
    }
}
