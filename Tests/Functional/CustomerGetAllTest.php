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
class CustomerGetAllTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canSendRequestDataProvider
     */
    public function testCanSendRequest(array $query, array $header): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/customers', $query, [], $header);

        self::assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function canSendRequestDataProvider(): iterable
    {
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];

        yield 'all set' => ['query' => ['page' => 1, 'perPage' => 500], 'header' => $validHeader];
        yield 'all set 2' => ['query' => ['page' => 10, 'perPage' => 1000], 'header' => $validHeader];
        yield 'no page' => ['query' => ['perPage' => 1000], 'header' => $validHeader];
        // TODO: fix problem with normalization and enum in query
        // yield 'no perPage' => ['query' => ['page' => 10], 'header' => $validHeader];
        // yield 'no query params' => ['query' => [], 'header' => $validHeader];
    }

    /**
     * @dataProvider failSendRequestDataProvider
     */
    public function testFailSendRequest(array $query, array $header): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/customers', $query, [], $header);

        self::assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function failSendRequestDataProvider(): iterable
    {
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];
        $validQuery = ['page' => 1, 'perPage' => 500];

        yield 'no auth token' => ['query' => ['page' => 1, 'perPage' => 500], 'header' => []];
        yield 'auth token 37' => [
            'query' => ['page' => 1, 'perPage' => 500],
            'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 37)],
        ];
        yield 'auth token 36' => [
            'query' => ['page' => 1, 'perPage' => 500],
            'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 35)],
        ];

        $incorrectQueryModifications = [
            'page not int' => ['page' => 'string'],
            'page negative' => ['page' => -10],
            'perPage incorrect enum' => ['perPage' => 110],
            'perPage not int' => ['perPage' => 'string'],
        ];

        foreach ($incorrectQueryModifications as $name => $modification) {
            yield $name => ['query' => array_merge($validQuery, $modification), 'header' => $validHeader];
        }
    }
}
