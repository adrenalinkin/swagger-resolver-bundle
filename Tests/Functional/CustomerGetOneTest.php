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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class CustomerGetOneTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canSendRequestDataProvider
     */
    public function testCanSendRequest($userId, array $header): void
    {
        $client = self::createClient();
        $client->request('GET', "/api/customers/{$userId}", [], [], $header);

        self::assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function canSendRequestDataProvider(): iterable
    {
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];

        yield 'min int' => ['userId' => 1, 'header' => $validHeader];
        yield 'max int' => ['userId' => \PHP_INT_MAX, 'header' => $validHeader];
    }

    /**
     * @dataProvider failSendRequestDataProvider
     */
    public function testFailSendRequest($userId, array $header): void
    {
        $client = self::createClient();
        $client->request('GET', "/api/customers/{$userId}", [], [], $header);

        self::assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function failSendRequestDataProvider(): iterable
    {
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];

        yield 'no auth token' => ['userId' => 1, 'header' => []];
        yield 'auth token 37' => ['userId' => 1, 'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 37)]];
        yield 'auth token 36' => ['userId' => 1, 'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 35)]];
        yield 'zero userId' => ['userId' => 0, 'header' => $validHeader];
    }
}
