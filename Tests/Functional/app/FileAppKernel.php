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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\app;

use Closure;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CartController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerPasswordController;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FileAppKernel extends AbstractKernel
{
    public function __construct(string $varDir, array $config, ?Closure $closure, string $environment, bool $debug)
    {
        parent::__construct($varDir, $config, $closure, $environment, $debug);

        $this->disableSwaggerPhp();
    }

    protected function getRouterConfig(): array
    {
        return [
            'resource' => $this->getProjectDir().'/app/routing.yaml',
        ];
    }

    protected function configureContainer(ContainerBuilder $container): void
    {
        $container->autowire(CartController::class)->addTag('controller.service_arguments');
        $container->autowire(CustomerController::class)->addTag('controller.service_arguments');
        $container->autowire(CustomerPasswordController::class)->addTag('controller.service_arguments');
    }

    private function disableSwaggerPhp(): void
    {
        $pathToLockFile = $this->getProjectDir().'/composer.lock';
        $rawData = file_get_contents($pathToLockFile);

        $originData = json_decode($rawData, true);
        $newData = $originData;
        $newData['packages-dev'] = [];

        foreach ($originData['packages-dev'] as $package) {
            if ('zircote/swagger-php' === $package['name']) {
                continue;
            }

            $newData['packages-dev'][] = $package;
        }

        file_put_contents($pathToLockFile, json_encode($newData, \JSON_UNESCAPED_SLASHES));
    }
}
