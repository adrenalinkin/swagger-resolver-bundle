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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional;

use Linkin\Bundle\SwaggerResolverBundle\Exception\OperationNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\NelmioApiDocConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\NelmioAppKernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocAreasIntegrationTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canWorkWithAreasDataProvider
     */
    public function testCanWorkWithAreas(array $areas, array $existedRoutes, array $notExistedRoutes): void
    {
        self::createClient([
            'config' => ['nelmio_api_doc' => ['areas' => $areas]],
            'kernelClass' => NelmioAppKernel::class,
        ]);

        $loader = self::getTestContainer()->get(NelmioApiDocConfigurationLoader::class);

        foreach ($existedRoutes as $routeName => $method) {
            self::assertNotNull($loader->getSchemaOperationCollection()->getSchema($routeName, $method));
        }

        foreach ($notExistedRoutes as $routeName => $method) {
            $this->expectException(OperationNotFoundException::class);
            $loader->getSchemaOperationCollection()->getSchema($routeName, $method);
        }
    }

    public function canWorkWithAreasDataProvider(): iterable
    {
        yield [
            'areas' => [],
            'existedRoutes' => [
                'cart_get' => 'get',
                'cart_add_item' => 'put',
                'customers_get' => 'get',
                'customers_post' => 'post',
                'customers_get_one' => 'get',
                'customers_update' => 'put',
                'customers_patch' => 'patch',
                'customers_delete' => 'delete',
                'customers_password_create' => 'post',
                'customers_password_reset' => 'put',
            ],
            'notExistedRoutes' => [],
        ];

        yield [
            'areas' => [
                'default' => ['path_patterns' => ['^/api/customers']],
            ],
            'existedRoutes' => [
                'customers_get' => 'get',
                'customers_post' => 'post',
                'customers_get_one' => 'get',
                'customers_update' => 'put',
                'customers_patch' => 'patch',
                'customers_delete' => 'delete',
                'customers_password_create' => 'post',
                'customers_password_reset' => 'put',
            ],
            'notExistedRoutes' => [
                'cart_get' => 'get',
                'cart_add_item' => 'put',
            ],
        ];

        yield [
            'areas' => [
                'default' => ['path_patterns' => ['^/api/customers']],
                'cart-only' => ['path_patterns' => ['^/api/cart']],
            ],
            'existedRoutes' => [
                'cart_get' => 'get',
                'cart_add_item' => 'put',
                'customers_get' => 'get',
                'customers_post' => 'post',
                'customers_get_one' => 'get',
                'customers_update' => 'put',
                'customers_patch' => 'patch',
                'customers_delete' => 'delete',
                'customers_password_create' => 'post',
                'customers_password_reset' => 'put',
            ],
            'notExistedRoutes' => [],
        ];

        yield [
            'areas' => [
                'default' => ['path_patterns' => ['^/api/customers/{userId}/password']],
                'cart-only' => ['path_patterns' => ['^/api/cart']],
            ],
            'existedRoutes' => [
                'cart_get' => 'get',
                'cart_add_item' => 'put',
                'customers_password_create' => 'post',
                'customers_password_reset' => 'put',
            ],
            'notExistedRoutes' => [
                'customers_get' => 'get',
                'customers_post' => 'post',
                'customers_get_one' => 'get',
                'customers_update' => 'put',
                'customers_patch' => 'patch',
                'customers_delete' => 'delete',
            ],
        ];
    }
}
