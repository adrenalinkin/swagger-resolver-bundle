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

use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CartController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerPasswordController;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerPhpAppKernel extends AbstractKernel
{
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

        $container->loadFromExtension('linkin_swagger_resolver', [
            'swagger_php' => [
                'exclude' => [
                    '%kernel.project_dir%/src/NelmioApiDocController',
                ],
            ],
        ]);
    }
}
