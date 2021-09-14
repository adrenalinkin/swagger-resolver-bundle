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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests;

use EXSyst\Component\Swagger\Schema;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerFactory
{
    /**
     * @param array $properties
     *
     * @example [
     *      'firstPropertyName' => [
     *          'type' => 'boolean',
     *      ],
     *      'secondPropertyName' => [
     *          'type' => 'integer',
     *      ]
     *  ]
     *
     * @return Schema
     */
    public static function createSchemaDefinition(array $properties): Schema
    {
        return new Schema([
            'type' => 'object',
            'properties' => $properties,
        ]);
    }

    /**
     * @param array $data
     *
     * @example [
     *      'type' => 'integer',
     *      'minimum' => 10,
     *  ]
     *
     * @return Schema
     */
    public static function createSchemaProperty(array $data): Schema
    {
        return new Schema($data);
    }
}
