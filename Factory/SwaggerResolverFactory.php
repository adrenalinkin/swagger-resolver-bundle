<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Factory;

use function end;
use function explode;
use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
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
     * @var SwaggerConfigurationInterface
     */
    private $swaggerConfiguration;

    public function __construct(
        SwaggerResolverBuilder $builder,
        SwaggerConfigurationInterface $configuration,
        RouterInterface $router
    ) {
        $this->builder = $builder;
        $this->swaggerConfiguration = $configuration;
        $this->router = $router;
    }

    public function createForRequest(Request $request): SwaggerResolver
    {
        $pathInfo = $this->router->match($request->getPathInfo());
        $routeName = $pathInfo['_route'];

        $mergedSchema = $this->swaggerConfiguration->getPathDefinition($routeName, $request->getMethod());

        return $this->builder->build($mergedSchema, $routeName);
    }

    public function createForDefinition(string $definitionName): SwaggerResolver
    {
        $explodedName = explode('\\', $definitionName);
        $definitionName = end($explodedName);

        $definition = $this->swaggerConfiguration->getDefinition($definitionName);

        return $this->builder->build($definition, $definitionName);
    }
}
