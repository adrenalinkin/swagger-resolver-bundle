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
class CustomerCreateTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canSendRequestDataProvider
     */
    public function testCanSendRequest(array $query, array $header, array $body): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/customers', $query, [], $header, json_encode($body));

        self::assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function canSendRequestDataProvider(): iterable
    {
        $validQuery = ['roles' => ['guest']];
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];
        $requiredFields = ['name', 'roles', 'email', 'password'];
        $validBody = [
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'password' => 'paSSword',
            'email' => 'homer@crud.com',
            'birthday' => '1965-05-12',
            'happyHour' => '14:00:00',
            'discount' => 30,
        ];

        yield 'full set' => ['query' => $validQuery, 'header' => $validHeader, 'body' => $validBody];
        yield 'no query' => ['query' => [], 'header' => $validHeader, 'body' => $validBody];
        yield '2 roles' => ['query' => ['guest', 'admin'], 'header' => $validHeader, 'body' => $validBody];

        $onlyRequired = [];
        foreach ($requiredFields as $name) {
            $onlyRequired[$name] = $validBody[$name];
        }

        yield 'body only required' => ['query' => $validQuery, 'header' => $validHeader, 'body' => $onlyRequired];
    }

    /**
     * @dataProvider failSendRequestDataProvider
     */
    public function testFailSendRequest(array $query, array $header, array $body): void
    {
        $client = self::createClient();
        $client->request('POST', '/api/customers', $query, [], $header, json_encode($body));

        self::assertSame(500, $client->getResponse()->getStatusCode());
    }

    public function failSendRequestDataProvider(): iterable
    {
        $validQuery = ['roles' => ['guest']];
        $validHeader = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];
        $validBody = [
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'password' => 'paSSword',
            'email' => 'homer@crud.com',
            'birthday' => '1965-05-12',
            'happyHour' => '14:00:00',
            'discount' => 30,
        ];
        $requiredFields = ['name', 'roles', 'email', 'password'];

        foreach ($requiredFields as $field) {
            $body = $validBody;
            unset($body[$field]);
            yield 'without '.$field => ['query' => $validQuery, 'header' => $validHeader, 'body' => $body];
        }

        yield 'auth token 35' => [
            'query' => $validQuery,
            'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 35)],
            'body' => $validBody,
        ];
        yield 'auth token 37' => [
            'query' => $validQuery,
            'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 37)],
            'body' => $validBody,
        ];

        $incorrectBodyModifications = [
            'short name' => ['name' => 's'],
            'long name' => ['name' => str_repeat('k', 51)],
            // 'undefined role' => ['roles' => ['undefined-role']], TODO: add Enum validation for array
            '0 items in role' => ['roles' => []],
            '3 items in role' => ['roles' => ['guest', 'user', 'admin']],
            'duplicate in role' => ['roles' => ['guest', 'guest']],
            'incorrect email' => ['email' => 'homer@gmail.com'],
            'incorrect date' => ['birthday' => '12.05.1965'],
            'incorrect date 2' => ['birthday' => '1965-02-30'],
            'incorrect time' => ['happyHour' => '14:00'],
            'discount incorrect multiple' => ['discount' => '9'],
            'discount greater 100' => ['discount' => '110'],
            'discount lower 0' => ['discount' => '-10'],
        ];

        foreach ($incorrectBodyModifications as $name => $modification) {
            yield $name => ['query' => [], 'header' => $validHeader, 'body' => array_merge($validBody, $modification)];
        }
    }
}
