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

use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app\SwaggerPhpAppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverWebTestCase extends WebTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove(self::varDir());
    }

    protected static function getTestContainer(): ContainerInterface
    {
        if (Kernel::VERSION_ID >= 40100) {
            return self::$container;
        }

        return self::$kernel->getContainer();
    }

    protected static function getKernelClass(string $kernelClass = SwaggerPhpAppKernel::class): string
    {
        return $kernelClass;
    }

    protected static function createKernel(array $options = [])
    {
        $class = static::getKernelClass($options['kernelClass'] ?? SwaggerPhpAppKernel::class);

        return new $class(
            self::varDir(),
            $options['config'] ?? [],
            $options['serviceClosure'] ?? null,
            $options['environment'] ?? 'test',
            $options['debug'] ?? true
        );
    }

    private static function varDir(): string
    {
        return sys_get_temp_dir().'/'.str_replace('\\', '_', static::class);
    }
}
