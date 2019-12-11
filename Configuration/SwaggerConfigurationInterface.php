<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Configuration;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Exception\PathNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
interface SwaggerConfigurationInterface
{
    /**
     * Return swagger definition schema object
     *
     * @param string $definitionName
     *
     * @return Schema
     *
     * @throws DefinitionNotFoundException When swagger definition was not found
     */
    public function getDefinition(string $definitionName): Schema;

    /**
     * Returns merged swagger path operation by @see OperationParameterMerger
     * according to specific @see MergeStrategyInterface
     *
     * @param string $routeName
     * @param string $method
     *
     * @return Schema
     *
     * @throws OperationNotFoundException   When swagger operation was not found
     * @throws PathNotFoundException        When swagger path was not found
     */
    public function getPathDefinition(string $routeName, string $method): Schema;
}
