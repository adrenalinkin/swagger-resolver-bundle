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

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use ReflectionClass;
use Symfony\Component\Config\Resource\FileResource;

use function explode;
use function reset;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractAnnotationConfigurationLoader extends AbstractSwaggerConfigurationLoader
{
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

    protected function getFileResource(string $className): FileResource
    {
        $class = new ReflectionClass($className);

        return new FileResource($class->getFileName());
    }
}
