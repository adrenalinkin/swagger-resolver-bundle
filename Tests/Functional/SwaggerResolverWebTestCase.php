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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverWebTestCase extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        (new Filesystem())->remove(self::varDir());
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem())->remove(self::varDir());
    }

    protected static function getTestContainer(): ContainerInterface
    {
        if (Kernel::VERSION_ID >= 40100) {
            return self::$container;
        }

        return self::$kernel->getContainer();
    }

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
            self::varDir(),
            $options['test_case'],
            $options['disable_swagger_php'] ?? false,
            $options['environment'] ?? $options['test_case'],
            $options['debug'] ?? true
        );
    }

    private static function varDir(): string
    {
        return sys_get_temp_dir().'/'.str_replace('\\', '_', static::class);
    }
}
