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

use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\FileAppKernel;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\NelmioAppKernel;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\SwaggerPhpAppKernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class CustomerCreateTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canSendRequestWithDifferentApplicationsDataProvider
     */
    public function testCanSendRequestWithDifferentApplications(array $configuration): void
    {
        $client = self::createClient($configuration);
        $expected = [
            'id' => 1,
        ];
        $query = [
            'roles' => ['guest'],
        ];
        $header = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];
        $body = [
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'password' => 'paSSword',
            'email' => 'homer@crud.com',
            'birthday' => '1965-05-12',
            'happyHour' => '14:00:00',
            'discount' => 30,
        ];

        $client->request('POST', '/api/customers', $query, [], $header, json_encode($body));

        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertSame($expected, json_decode($client->getResponse()->getContent(), true));
    }

    public function canSendRequestWithDifferentApplicationsDataProvider(): iterable
    {
        yield [['kernelClass' => NelmioAppKernel::class]];
        yield [['kernelClass' => SwaggerPhpAppKernel::class]];
        yield [['kernelClass' => FileAppKernel::class]];
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
            yield ['query' => [], 'header' => $validHeader, 'body' => $body];
        }

        yield ['query' => [], 'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 35)], 'body' => $validBody];
        yield ['query' => [], 'header' => ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 37)], 'body' => $validBody];

        $incorrectBodyModifications = [
            ['name' => 's'],
            ['name' => str_repeat('k', 51)],
            // ['roles' => ['undefined-role']], TODO: add Enum validation for array
            ['roles' => []],
            ['roles' => ['guest', 'user', 'admin']],
            ['roles' => ['guest', 'guest']],
            ['email' => 'homer@gmail.com'],
            ['birthday' => '12.05.1965'],
            ['birthday' => '1965-02-30'],
            ['happyHour' => '14:00'],
            ['discount' => '9'],
            ['discount' => '110'],
            ['discount' => '-10'],
        ];

        foreach ($incorrectBodyModifications as $modification) {
            yield ['query' => [], 'header' => $validHeader, 'body' => array_merge($validBody, $modification)];
        }
    }
}
