<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;

interface SwaggerConfigurationLoaderInterface
{
    /**
     * Return swagger definition schema collection
     *
     * @return SchemaDefinitionCollection
     */
    public function getSchemaDefinitionCollection(): SchemaDefinitionCollection;

    /**
     * Returns collection of the merged swagger path operation by @see OperationParameterMerger
     * according to specific @see MergeStrategyInterface
     *
     * @return SchemaOperationCollection
     */
    public function getSchemaOperationCollection(): SchemaOperationCollection;
}
