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

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use function end;
use function explode;
use function get_declared_classes;
use function reset;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractAnnotationConfigurationLoader extends AbstractSwaggerConfigurationLoader
{
    /**
     * @var Route[]
     */
    private $routerCollection;

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
        parent::__construct($parameterMerger, $router);

        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void
    {
        $definitionNames = [];

        foreach ($definitionCollection->getIterator() as $definitionName => $definition) {
            $definitionNames[$definitionName] = $definitionName;
        }

        foreach (get_declared_classes() as $fullClassName) {
            $explodedClassName = explode('\\', $fullClassName);
            $className = end($explodedClassName);

            if (!isset($definitionNames[$className])) {
                continue;
            }

            $definitionCollection->addSchemaResource($className, $this->getFileResource($fullClassName));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerOperationResources(SchemaOperationCollection $operationCollection): void
    {
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            $this->routerCollection[$routeName] = $route;
        }

        foreach ($operationCollection->getIterator() as $routeName => $methodList) {
            if (empty($this->routerCollection[$routeName])) {
                continue;
            }

            $route = $this->routerCollection[$routeName];
            $defaults = $route->getDefaults();
            $exploded = explode('::', $defaults['_controller']);
            $controllerName = reset($exploded);

            $operationCollection->addSchemaResource($routeName, $this->getFileResource($controllerName));
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
}
