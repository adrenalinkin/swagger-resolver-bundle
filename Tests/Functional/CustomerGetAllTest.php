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
    public function testCanSendRequest(): void
    {
        $client = self::createClient();
        $expected = [
            'discount' => 30,
            'rating' => 3.5,
            'id' => 1,
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'email' => 'homer@crud.com',
            'isEmailConfirmed' => true,
            'birthday' => '1965-05-12',
            'happyHour' => '14:00:00',
            'registeredAt' => '2000-10-11T19:57:31Z',
            'lastVisitedAt' => '665701200',
        ];

        $query = ['page' => 1, 'perPage' => 100];
        $header = ['HTTP_X_AUTH_TOKEN' => str_repeat('k', 36)];
        $client->request('GET', '/api/customers', $query, [], $header);

        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertSame([$expected], json_decode($client->getResponse()->getContent(), true));
    }
}
