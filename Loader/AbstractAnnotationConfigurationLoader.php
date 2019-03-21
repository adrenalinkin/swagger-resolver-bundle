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

use EXSyst\Component\Swagger\Collections\Definitions;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Swagger;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use function explode;
use function reset;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractAnnotationConfigurationLoader implements SwaggerConfigurationLoaderInterface
{
    /**
     * @var FileResource[][]
     */
    private $resources;

    /**
     * @var Route[]
     */
    private $routerCollection;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        foreach ($router->getRouteCollection() as $routeName => $route) {
            $this->routerCollection[$route->getPath()] = $route;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileResources(string $definitionName): array
    {
        return $this->resources[$definitionName] ?? [];
    }

    /**
     * @param Swagger $swagger
     *
     * @throws ReflectionException
     */
    protected function registerResources(Swagger $swagger): void
    {
        $this->registerDefinitionResources($swagger->getDefinitions());

        /** @var Path $pathObject */
        foreach ($swagger->getPaths()->getIterator() as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                $this->registerPathResources($path, $operation);
            }
        }
    }

    /**
     * @param string $className
     *
     * @return FileResource
     *
     * @throws ReflectionException
     */
    private function getFileResource(string $className)
    {
        $class = new ReflectionClass($className);

        return new FileResource($class->getFileName());
    }

    /**
     * @param Definitions $definitions
     *
     * @throws ReflectionException
     */
    private function registerDefinitionResources(Definitions $definitions): void
    {
        $definitionNames = [];

        foreach ($definitions->getIterator() as $definitionName => $definition) {
            $definitionNames[$definitionName] = $definitionName;
        }

        foreach (get_declared_classes() as $fullClassName) {
            $explodedClassName = explode('\\', $fullClassName);
            $className = end($explodedClassName);

            if (!isset($definitionNames[$className])) {
                continue;
            }

            $this->resources[$className][] = $this->getFileResource($fullClassName);
        }
    }

    /**
     * @param string $path
     * @param Operation $operation
     *
     * @throws ReflectionException
     */
    private function registerPathResources(string $path, Operation $operation): void
    {
        $route = $this->routerCollection[$path];
        $defaults = $route->getDefaults();
        $exploded = explode('::', $defaults['_controller']);
        $controllerName = reset($exploded);

        $this->resources[$path][] = $this->getFileResource($controllerName);

        /** @var Parameter $parameter */
        foreach ($operation->getParameters()->getIterator() as $name => $parameter) {
            $ref = $parameter->getSchema()->getRef();

            if (!$ref) {
                continue;
            }

            $explodedName = explode('/', $ref);
            $definitionName = end($explodedName);

            $refResources = $this->resources[$definitionName] ?? [];

            foreach ($refResources as $fileResource) {
                $this->resources[$path][] = $fileResource;
            }
        }
    }
}
