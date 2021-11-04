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
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Resource\FileResource;

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
     * {@inheritdoc}
     */
    protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void
    {
        $definitionNames = [];

        foreach ($definitionCollection->getIterator() as $definitionName => $definition) {
            $definitionName = (string) $definitionName;
            $definitionNames[$definitionName] = $definitionName;
        }

        foreach (get_declared_classes() as $fullClassName) {
            $explodedClassName = explode('\\', $fullClassName);
            $className = (string) end($explodedClassName);

            if (!isset($definitionNames[$className])) {
                continue;
            }

            $definitionCollection->addSchemaResource($className, $this->getFileResource($fullClassName));
        }

        // TODO: Throw exception when class was never found
    }

    /**
     * {@inheritdoc}
     */
    protected function registerOperationResources(SchemaOperationCollection $operationCollection): void
    {
        foreach ($operationCollection->getIterator() as $routeName => $methodList) {
            $route = $this->getRouter()->getRouteCollection()->get($routeName);

            if ($route === null) {
                continue;
            }

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
