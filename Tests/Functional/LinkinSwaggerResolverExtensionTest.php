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
    public function testCanApplyNelmioApiDocByDefault(): void
    {
        $this->assertByCallRoute('NelmioApiDoc');
    }

    public function testCanApplyFallbackToSwaggerPhp(): void
    {
        $this->assertByCallRoute('SwaggerPhp');
    }

    public function testCanApplyFallbackToJsonFile(): void
    {
        $pathToVendor = __DIR__.'/../../vendor';

        exec("mv $pathToVendor/zircote $pathToVendor/tmp-zircote");
        exec('composer dump-autoload');

        $this->assertByCallRoute('Json');

        exec("mv $pathToVendor/tmp-zircote $pathToVendor/zircote");
        exec('composer dump-autoload');
    }

    private function assertByCallRoute(string $testCase): void
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
}
