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
use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FixturesProvider
{
    public const PATH_TO_SWG_JSON = __DIR__ . '/Functional/web/swagger.json';
    public const PATH_TO_SWG_YAML = __DIR__ . '/Functional/web/swagger.yaml';

    private const MAP_DEFINITION_RESOURCE = [
        'Cart' => __DIR__.'/Functional/src/Models/Cart.php',
        'CartItem' => __DIR__.'/Functional/src/Models/CartItem.php',
        'CustomerFull' => __DIR__.'/Functional/src/Models/CustomerFull.php',
        'CustomerNew' => __DIR__.'/Functional/src/Models/CustomerNew.php',
        'ResponseCreated' => __DIR__.'/Functional/src/Models/ResponseCreated.php',
    ];

    private const MAP_ROUTE_RESOURCE = [
        'cart_add_item' => [
            __DIR__.'/Functional/src/Models/CartItem.php',
            __DIR__.'/Functional/src/SwaggerPhpController/CartController.php',
        ],
        'cart_get' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CartController.php',
        ],
        'customers_get' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_post' => [
            __DIR__.'/Functional/src/Models/CustomerNew.php',
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_get_one' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_update' => [
            __DIR__.'/Functional/src/Models/CustomerNew.php',
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_patch' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_delete' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerController.php',
        ],
        'customers_password_create' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerPasswordController.php',
            ],
        'customers_password_reset' => [
            __DIR__.'/Functional/src/SwaggerPhpController/CustomerPasswordController.php',
        ],
    ];

    private const MAP_PATH_TO_ROUTE = [
        '/api/cart' => [
            'get' => 'customers_get',
            'put' => 'cart_add_item',
        ],
        '/api/customers' => [
            'get' => 'customers_get',
            'post' => 'customers_post',
        ],
        '/api/customers/{userId}' => [
            'get' => 'customers_get_one',
            'put' => 'customers_update',
            'patch' => 'customers_patch',
            'delete' => 'customers_delete',
        ],
        '/api/customers/{userId}/password' => [
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
            self::$cachedSwagger = Swagger::fromFile(self::PATH_TO_SWG_JSON);
        }

        return self::$cachedSwagger;
    }

    public static function createRouter(?RequestContext $context = null): Router
    {
        return new Router(
            new YamlFileLoader(new FileLocator(__DIR__.'/Functional/app/default')),
            'routing.yaml',
            [],
            $context
        );
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
