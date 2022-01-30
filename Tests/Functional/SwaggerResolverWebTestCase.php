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

use InvalidArgumentException;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\TestAppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverWebTestCase extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/app/TestAppKernel.php';

        return TestAppKernel::class;
    }

    protected static function createKernel(array $options = [])
    {
        $class = self::getKernelClass();

        if (empty($options['test_case'])) {
            throw new InvalidArgumentException('The option "test_case" must be set.');
        }

        return new $class(
            $options['test_case'],
            $options['environment'] ?? $options['test_case'],
            $options['debug'] ?? true
        );
    }
}
