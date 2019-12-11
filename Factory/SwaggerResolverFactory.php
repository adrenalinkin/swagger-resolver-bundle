<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Factory;

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use function end;
use function explode;
use function strtolower;

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
        $routeName = $pathInfo['_route'];
        $method = strtolower($request->getMethod());

        $mergedSchema = $this->swaggerConfiguration->getPathDefinition($routeName, $method);

        return $this->builder->build($mergedSchema, $routeName);
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
