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

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfiguration;
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
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SwaggerConfiguration
     */
    private $swaggerConfiguration;

    /**
     * @param SwaggerResolverBuilder $builder
     * @param SwaggerConfiguration $configuration
     * @param RouterInterface $router
     */
    public function __construct(
        SwaggerResolverBuilder $builder,
        SwaggerConfiguration $configuration,
        RouterInterface $router
    ) {
        $this->builder = $builder;
        $this->swaggerConfiguration = $configuration;
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return SwaggerResolver
     */
    public function createForRequest(Request $request): SwaggerResolver
    {
        $pathInfo = $this->router->match($request->getPathInfo());
        $route = $this->router->getRouteCollection()->get($pathInfo['_route']);
        $routePath = $route->getPath();

        $mergedSchema = $this->swaggerConfiguration->getPathDefinition($routePath, $request->getMethod());

        return $this->builder->build($mergedSchema, $routePath);
    }

    /**
     * @param string $definitionName
     *
     * @return SwaggerResolver
     */
    public function createForDefinition(string $definitionName): SwaggerResolver
    {
        $definition = $this->swaggerConfiguration->getDefinition($definitionName);

        return $this->builder->build($definition, $definitionName);
    }
}
