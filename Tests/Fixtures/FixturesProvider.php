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
    private const MAP_DEFINITION_RESOURCE = [
        'Cart' => __DIR__.'/../Functional/Bundle/TestBundle/Models/Cart.php',
        'CartItem' => __DIR__.'/../Functional/Bundle/TestBundle/Models/CartItem.php',
        'CustomerFull' => __DIR__.'/../Functional/Bundle/TestBundle/Models/CustomerFull.php',
        'CustomerNew' => __DIR__.'/../Functional/Bundle/TestBundle/Models/CustomerNew.php',
        'ResponseCreated' => __DIR__.'/../Functional/Bundle/TestBundle/Models/ResponseCreated.php',
    ];

    private const MAP_ROUTE_RESOURCE = [
        'cart_add_item' => [
            __DIR__.'/../Functional/Bundle/TestBundle/Models/CartItem.php',
            __DIR__.'/SwaggerPhp/Controllers/CartController.php',
        ],
        'cart_get' => [
            __DIR__.'/SwaggerPhp/Controllers/CartController.php',
        ],
        'customers_get' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_post' => [
            __DIR__.'/../Functional/Bundle/TestBundle/Models/CustomerNew.php',
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_get_one' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_update' => [
            __DIR__.'/../Functional/Bundle/TestBundle/Models/CustomerNew.php',
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_patch' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_delete' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerController.php',
        ],
        'customers_password_create' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerPasswordController.php',
            ],
        'customers_password_reset' => [
            __DIR__.'/SwaggerPhp/Controllers/CustomerPasswordController.php',
        ],
    ];

    private const MAP_PATH_TO_ROUTE = [
        '/cart' => [
            'get' => 'customers_get',
            'put' => 'cart_add_item',
        ],
        '/customers' => [
            'get' => 'customers_get',
            'post' => 'customers_post',
        ],
        '/customers/{userId}' => [
            'get' => 'customers_get_one',
            'put' => 'customers_update',
            'patch' => 'customers_patch',
            'delete' => 'customers_delete',
        ],
        '/customers/{userId}/password' => [
            'post' => 'customers_password_create',
            'put' => 'customers_password_reset',
        ],
    ];

    /**
     * @var Swagger
     */
    private static $cachedSwagger;

    public static function getRouteName(string $path, string $method): string
    {
        return self::MAP_PATH_TO_ROUTE[$path][$method];
    }

    public static function getResourceByRouteName(string $routeName): array
    {
        $result = [];
        foreach (self::MAP_ROUTE_RESOURCE[$routeName] as $path) {
            $result[] = realpath($path);
        }

        return $result;
    }

    public static function getResourceByDefinition(string $definitionName): string
    {
        return realpath(self::MAP_DEFINITION_RESOURCE[$definitionName]);
    }

    public static function loadFromJson(): Swagger
    {
        if (null === self::$cachedSwagger) {
            self::$cachedSwagger = Swagger::fromFile(__DIR__.'/Json/customer.json');
        }

        return self::$cachedSwagger;
    }

    /**
     * @example [
     *      'firstPropertyName' => [
     *          'type' => 'boolean',
     *      ],
     *      'secondPropertyName' => [
     *          'type' => 'integer',
     *      ]
     *  ]
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
     * @example [
     *      'type' => 'integer',
     *      'minimum' => 10,
     *  ]
     */
    public static function createSchemaProperty(array $data): Schema
    {
        return new Schema($data);
    }
}
