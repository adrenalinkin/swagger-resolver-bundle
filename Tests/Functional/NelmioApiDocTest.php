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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocTest extends WebTestCase
{
    public function testCorrectlyLoaded(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/customers');

        $response = $client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
    }
}
