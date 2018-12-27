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

namespace Linkin\Bundle\SwaggerResolverBundle\Factory;

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\PathParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverFactory
{
    /**
     * @var SwaggerResolverBuilder
     */
    private $builder;

    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @var PathParameterMerger
     */
    private $pathParameterMerger;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Swagger
     */
    private $swaggerConfiguration;

    /**
     * @param SwaggerResolverBuilder $builder
     * @param SwaggerConfigurationLoaderInterface $loader
     * @param PathParameterMerger $pathParameterMerger
     * @param RouterInterface $router
     */
    public function __construct(
        SwaggerResolverBuilder $builder,
        SwaggerConfigurationLoaderInterface $loader,
        PathParameterMerger $pathParameterMerger,
        RouterInterface $router
    ) {
        $this->builder = $builder;
        $this->configurationLoader = $loader;
        $this->pathParameterMerger = $pathParameterMerger;
        $this->router = $router;
    }

    /**
     * @param Request $request
     * @param MergeStrategyInterface|null $mergeStrategy
     *
     * @return SwaggerResolver
     */
    public function createForRequest(Request $request, ?MergeStrategyInterface $mergeStrategy = null): SwaggerResolver
    {
        $pathInfo = $this->router->match($request->getPathInfo());
        $route = $this->router->getRouteCollection()->get($pathInfo['_route']);
        $routePath = $route->getPath();

        $paths = $this->getSwaggerConfiguration()->getPaths();
        $definitions = $this->getSwaggerConfiguration()->getDefinitions();

        if (!$paths->has($routePath)) {
            throw new PathNotFoundException($routePath);
        }

        $requestMethod = strtolower($request->getMethod());

        $swaggerPath = $paths->get($routePath);

        $mergedSchema = $this->pathParameterMerger->merge($swaggerPath, $requestMethod, $definitions, $mergeStrategy);

        return $this->builder->build($mergedSchema, $routePath);
    }

    /**
     * @param string $definitionName
     *
     * @return SwaggerResolver
     */
    public function createForDefinition(string $definitionName): SwaggerResolver
    {
        $definition = $this->getDefinition($definitionName);

        return $this->builder->build($definition, $definitionName);
    }

    /**
     * @return Swagger
     */
    private function getSwaggerConfiguration(): Swagger
    {
        if (null === $this->swaggerConfiguration) {
            $this->swaggerConfiguration = $this->configurationLoader->loadConfiguration();
        }

        return $this->swaggerConfiguration;
    }

    /**
     * @param string $definitionName
     *
     * @return Schema
     */
    private function getDefinition(string $definitionName): Schema
    {
        $definitions = $this->getSwaggerConfiguration()->getDefinitions();

        $explodedName = explode('\\', $definitionName);
        $definitionName = end($explodedName);

        if ($definitions->has($definitionName)) {
            return $definitions->get($definitionName);
        }

        throw new DefinitionNotFoundException($definitionName);
    }
}
