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
class LinkinSwaggerResolverExtensionTest extends SwaggerResolverWebTestCase
{
    /**
     * @dataProvider canApplyDifferentLoadersDataProvider
     */
    public function testCanApplyDifferentLoaders(string $testCase): void
    {
        $data = [
            'id' => 1,
            'name' => 'Homer',
            'roles' => ['guest'],
            'email' => 'homer@crud.com',
            'isEmailConfirmed' => true,
            'registeredAt' => '2000-10-11T19:57:31Z',
        ];

        $client = self::createClient(['test_case' => $testCase]);
        $client->request('GET', '/api/customers', [], [], [], json_encode($data));

        $response = $client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
    }

    public function canApplyDifferentLoadersDataProvider(): iterable
    {
        yield ['NelmioApiDoc'];
        yield ['SwaggerPhp'];
    }
}
