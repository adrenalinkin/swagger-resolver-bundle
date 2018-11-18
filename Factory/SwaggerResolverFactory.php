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

use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
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
     * @param RouterInterface $router
     */
    public function __construct(
        SwaggerResolverBuilder $builder,
        SwaggerConfigurationLoaderInterface $loader,
        RouterInterface $router
    ) {
        $this->builder = $builder;
        $this->configurationLoader = $loader;
        $this->router = $router;
    }

    /**
     * @TODO: Refactor this method and add strategies for merging request parameters
     *
     * @param Request $request
     *
     * @return SwaggerResolver
     */
    public function createForRequest(Request $request): SwaggerResolver
    {
        $pathInfo = $this->router->match($request->getPathInfo());
        $route = $this->router->getRouteCollection()->get($pathInfo['_route']);
        $routePath = $route->getPath();

        $paths = $this->getSwaggerConfiguration()->getPaths();

        if (!$paths->has($routePath)) {
            throw new PathNotFoundException($routePath);
        }

        $requestMethod = strtolower($request->getMethod());

        $swaggerPath = $paths->get($routePath);
        $swaggerOperation = $swaggerPath->getOperation($requestMethod);
        $parameters = $swaggerOperation->getParameters();

        $mergedProperties = [];
        $requiredList = [];

        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            if ($parameter->getIn() === 'body') {
                $parameterSchema = $parameter->getSchema();

                $ref = $parameterSchema->getRef();

                if ($ref) {
                    $explodedName = explode('/', $ref);
                    $definitionName = end($explodedName);

                    $refDefinition = $this->getDefinition($definitionName);
                    $requiredList += $refDefinition->getRequired() ?? [];

                    foreach ($refDefinition->getProperties() as $defName => $defItem) {
                        $mergedProperties[$defName] = $defItem->toArray();
                    }

                    continue;
                }

                // body as object
                if ($parameterSchema->getType() === 'object') {
                    $requiredList += $parameterSchema->getRequired() ?? [];

                    foreach ($parameterSchema->getProperties() as $bodyItemName => $currentBodyItem) {
                        $mergedProperties[$bodyItemName] = $currentBodyItem->toArray();
                    }

                    continue;
                }

                // as scalar
                $asArray = $parameterSchema->toArray();
                $asArray['in'] = $parameter->getIn();
                $asArray['name'] = $parameter->getName();
                $asArray['required'] = $parameter->getRequired();

                $mergedProperties[$parameter->getName()] = $asArray;

                continue;
            }

            if ($parameter->getRequired() === true) {
                $requiredList[] = $parameter->getName();
            }

            $mergedProperties[$parameter->getName()] = $parameter->toArray();
        }

        $mergedSchema = new Schema();
        $mergedSchema->merge([
            'type' => 'object',
            'description' => '__SwaggerResolver_merged_query_properties__',
            'properties' => $mergedProperties,
            'required' => $requiredList,
        ]);

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
