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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures;

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FixturesProvider
{
    /**
     * @var Swagger
     */
    private static $cachedSwagger;

    public static function loadFromJson(): Swagger
    {
        if (self::$cachedSwagger === null) {
            self::$cachedSwagger = Swagger::fromFile(__DIR__ . '/Json/customer.json');
        }

        return self::$cachedSwagger;
    }

    /**
     * @param array $properties
     * @param array $required
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
    public static function createSchemaDefinition(array $properties, array $required = []): Schema
    {
        return new Schema([
            'type' => 'object',
            'required' => $required,
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