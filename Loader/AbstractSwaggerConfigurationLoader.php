<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use function end;
use function explode;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use function strtolower;
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
     * @var array
     */
    private $mapPathToRouteName;

    /**
     * @var OperationParameterMerger
     */
    private $parameterMerger;

    /**
     * @var RouterInterface
     */
    private $router;

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
    abstract protected function registerOperationResources(SchemaOperationCollection $operationCollection): void;

    protected function getRouter(): RouterInterface
    {
        return $this->router;
    }

    protected function getRouteNameByPath(string $path, string $method): string
    {
        if (!$this->mapPathToRouteName) {
            $this->initMapPathToRouteName();
        }

        $route = $this->mapPathToRouteName[$path][$method] ?? null;

        if (!$route) {
            throw new OperationNotFoundException($path, $method);
        }

        return (string) $route;
    }

    /**
     * Initialize map real path into appropriated route name.
     */
    private function initMapPathToRouteName(): void
    {
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            foreach ($route->getMethods() as $method) {
                $method = $this->normalizeMethod($method);
                $this->mapPathToRouteName[$route->getPath()][$method] = $routeName;
            }
        }
    }

    /**
     * Register collection according to loaded Swagger object.
     */
    private function registerCollections(): void
    {
        $swaggerConfiguration = $this->loadConfiguration();

        $definitionCollection = new SchemaDefinitionCollection();
        $operationCollection = new SchemaOperationCollection();

        foreach ($swaggerConfiguration->getDefinitions()->getIterator() as $definitionName => $definition) {
            $definitionCollection->addSchema($definitionName, $definition);
        }

        $this->registerDefinitionResources($definitionCollection);

        /** @var Path $pathObject */
        foreach ($swaggerConfiguration->getPaths()->getIterator() as $path => $pathObject) {
            /** @var Operation $operation */
            foreach ($pathObject->getOperations() as $method => $operation) {
                $method = $this->normalizeMethod($method);
                $schema = $this->parameterMerger->merge($operation, $swaggerConfiguration->getDefinitions());
                $routeName = $this->getRouteNameByPath($path, $method);
                $operationCollection->addSchema($routeName, $method, $schema);

                /** @var Parameter $parameter */
                foreach ($operation->getParameters()->getIterator() as $parameter) {
                    $ref = $parameter->getSchema()->getRef();

                    if (!\is_string($ref)) {
                        continue;
                    }

                    $explodedName = explode('/', $ref);
                    $definitionName = end($explodedName);

                    foreach ($definitionCollection->getSchemaResources($definitionName) as $fileResource) {
                        $operationCollection->addSchemaResource($routeName, $fileResource);
                    }
                }
            }
        }

        $this->registerOperationResources($operationCollection);

        $this->definitionCollection = $definitionCollection;
        $this->operationCollection = $operationCollection;
    }

    private function normalizeMethod(string $method): string
    {
        return strtolower($method);
    }
}
