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
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Symfony\Component\Routing\RouterInterface;
use function end;
use function explode;

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
     * @var RouterInterface $router
     */
    private $router;

    /**
     * @param OperationParameterMerger $parameterMerger
     * @param RouterInterface $router
     */
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
     * Load full configuration and returns Swagger object
     *
     * @return Swagger
     */
    abstract protected function loadConfiguration(): Swagger;

    /**
     * Add file resources for swagger definitions
     *
     * @param SchemaDefinitionCollection $definitionCollection
     */
    abstract protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void;

    /**
     * Add file resources for swagger operations
     *
     * @param SchemaOperationCollection $operationCollection
     */
    abstract protected function registerOperationResources(SchemaOperationCollection $operationCollection): void;

    /**
     * @param string $path
     *
     * @return string
     */
    protected function getRouteNameByPath(string $path): string
    {
        if (empty($this->mapPathToRouteName)) {
            foreach ($this->router->getRouteCollection() as $routeName => $route) {
                $this->mapPathToRouteName[$route->getPath()] = $routeName;
            }
        }

        $route = $this->mapPathToRouteName[$path] ?? null;

        if (!$route) {
            throw new PathNotFoundException($path);
        }

        return $this->mapPathToRouteName[$path];
    }

    /**
     * Register collection according to loaded Swagger object
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
                $schema = $this->parameterMerger->merge($operation, $swaggerConfiguration->getDefinitions());
                $operationCollection->addSchema($this->getRouteNameByPath($path), $method, $schema);

                /** @var Parameter $parameter */
                foreach ($operation->getParameters()->getIterator() as $name => $parameter) {
                    $ref = $parameter->getSchema()->getRef();

                    if (!$ref) {
                        continue;
                    }

                    $explodedName = explode('/', $ref);
                    $definitionName = end($explodedName);

                    foreach ($definitionCollection->getSchemaResources($definitionName) as $fileResource) {
                        $operationCollection->addSchemaResource($this->getRouteNameByPath($path), $fileResource);
                    }
                }
            }
        }

        $this->registerOperationResources($operationCollection);

        $this->definitionCollection = $definitionCollection;
        $this->operationCollection = $operationCollection;
    }
}
