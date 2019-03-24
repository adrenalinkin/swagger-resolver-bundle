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

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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
