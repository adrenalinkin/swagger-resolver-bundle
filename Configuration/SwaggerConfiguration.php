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

use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerConfiguration
{
    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @var OperationParameterMerger
     */
    private $parameterMerger;

    /**
     * @var Schema[]
     */
    private $schemaDefinitionList;

    /**
     * @var Schema[]
     */
    private $schemaOperationList;

    /**
     * @param OperationParameterMerger $parameterMerger
     * @param SwaggerConfigurationLoaderInterface $loader
     */
    public function __construct(OperationParameterMerger $parameterMerger, SwaggerConfigurationLoaderInterface $loader)
    {
        $this->configurationLoader = $loader;
        $this->parameterMerger = $parameterMerger;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $definitionName): Schema
    {
        $schemaDefinitionList = $this->getSchemaDefinitionList();

        if (isset($schemaDefinitionList[$definitionName])) {
            return $schemaDefinitionList[$definitionName];
        }

        throw new DefinitionNotFoundException($definitionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPathDefinition(string $routePath, string $method): Schema
    {
        $schemaOperationList = $this->getSchemaOperationList();

        if (empty($schemaOperationList[$routePath])) {
            throw new PathNotFoundException($routePath);
        }

        if (empty($schemaOperationList[$routePath][$method])) {
            throw new OperationNotFoundException($routePath, $method);
        }

        return $schemaOperationList[$routePath][$method];
    }

    /**
     * @return Schema[]
     */
    protected function getSchemaDefinitionList(): array
    {
        if ($this->schemaDefinitionList === null) {
            $this->loadConfiguration();
        }

        return $this->schemaDefinitionList;
    }

    /**
     * @return Schema[]
     */
    protected function getSchemaOperationList(): array
    {
        if ($this->schemaOperationList === null) {
            $this->loadConfiguration();
        }

        return $this->schemaOperationList;
    }

    /**
     * Load full configuration
     */
    private function loadConfiguration(): void
    {
        $configuration = $this->configurationLoader->loadConfiguration();
        $definitions = $configuration->getDefinitions();

        foreach ($configuration->getDefinitions()->getIterator() as $definitionName => $definition) {
            $this->schemaDefinitionList[$definitionName] = $definition;
        }

        /** @var Path $pathObject */
        foreach ($configuration->getPaths()->getIterator() as $path => $pathObject) {
            foreach ($pathObject->getOperations() as $method => $operation) {
                $this->schemaOperationList[$path][$method] = $this->parameterMerger->merge($operation, $definitions);
            }
        }
    }
}
