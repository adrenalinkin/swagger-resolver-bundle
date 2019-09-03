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
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use function end;
use function explode;
use function strtolower;

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
     * @var SwaggerConfigurationInterface
     */
    private $swaggerConfiguration;

    /**
     * @param SwaggerResolverBuilder $builder
     * @param SwaggerConfigurationInterface $configuration
     * @param RouterInterface $router
     */
    public function __construct(
        SwaggerResolverBuilder $builder,
        SwaggerConfigurationInterface $configuration,
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
        $routeAlias = $pathInfo['_route'];
        $method = strtolower($request->getMethod());

        $mergedSchema = $this->swaggerConfiguration->getPathDefinition($routeAlias, $method);

        return $this->builder->build($mergedSchema, $routeAlias);
    }

    /**
     * @param string $definitionName
     *
     * @return SwaggerResolver
     */
    public function createForDefinition(string $definitionName): SwaggerResolver
    {
        $explodedName = explode('\\', $definitionName);
        $definitionName = end($explodedName);

        $definition = $this->swaggerConfiguration->getDefinition($definitionName);

        return $this->builder->build($definition, $definitionName);
    }
}
