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

namespace Linkin\Bundle\SwaggerResolverBundle\Configuration;

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\PathParameterMerger;
use function end;
use function explode;
use function strtolower;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerConfiguration
{
    /**
     * @var Swagger
     */
    private $configuration;

    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @var PathParameterMerger
     */
    private $parameterMerger;

    /**
     * @param PathParameterMerger $parameterMerger
     * @param SwaggerConfigurationLoaderInterface $loader
     */
    public function __construct(PathParameterMerger $parameterMerger, SwaggerConfigurationLoaderInterface $loader)
    {
        $this->configurationLoader = $loader;
        $this->parameterMerger = $parameterMerger;
    }

    /**
     * @param string $definitionName
     *
     * @return Schema
     *
     * @throws DefinitionNotFoundException
     */
    public function getDefinition(string $definitionName): Schema
    {
        $definitions = $this->getConfiguration()->getDefinitions();

        $explodedName = explode('\\', $definitionName);
        $definitionName = end($explodedName);

        if ($definitions->has($definitionName)) {
            return $definitions->get($definitionName);
        }

        throw new DefinitionNotFoundException($definitionName);
    }

    /**
     * @param string $routePath
     * @param string $method
     *
     * @return Schema
     *
     * @throws PathNotFoundException
     */
    public function getPathDefinition(string $routePath, string $method): Schema
    {
        $paths = $this->getConfiguration()->getPaths();

        if (!$paths->has($routePath)) {
            throw new PathNotFoundException($routePath);
        }

        $definitions = $this->getConfiguration()->getDefinitions();
        $swaggerPath = $paths->get($routePath);
        $requestMethod = strtolower($method);

        return $this->parameterMerger->merge($swaggerPath, $requestMethod, $definitions);
    }

    /**
     * @return Swagger
     */
    private function getConfiguration(): Swagger
    {
        if (null === $this->configuration) {
            $this->configuration = $this->configurationLoader->loadConfiguration();
        }

        return $this->configuration;
    }
}
