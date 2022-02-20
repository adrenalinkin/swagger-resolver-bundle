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

use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CartController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CustomerController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CustomerPasswordController;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioAppKernel extends AbstractKernel
{
    public function registerBundles(): array
    {
        $bundles = parent::registerBundles();
        $bundles[] = new NelmioApiDocBundle();

        return $bundles;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import($this->getProjectDir().'/src/NelmioApiDocController', '/api', 'annotation');
    }

    protected function configureContainer(ContainerBuilder $container): void
    {
        $container->loadFromExtension('linkin_swagger_resolver', array_merge([
            'path_merge_strategy' => ReplaceLastWinMergeStrategy::class,
        ], $this->config));

        $container->autowire(CartController::class)->addTag('controller.service_arguments');
        $container->autowire(CustomerController::class)->addTag('controller.service_arguments');
        $container->autowire(CustomerPasswordController::class)->addTag('controller.service_arguments');
        $container->loadFromExtension('nelmio_api_doc', [
            'documentation' => [
                'swagger' => '2.0',
                'host' => 'localhost',
                'schemes' => ['https'],
                'info' => [
                    'version' => '1.0.0',
                    'title' => 'Customer API',
                    'description' => 'Example API for work with customer',
                ],
                'consumes' => ['application/json'],
                'produces' => ['application/json'],
            ],
            // TODO: project should work without areas definition
            'areas' => [
                'default' => [
                    'path_patterns' => [
                        '^/api/',
                    ],
                ],
            ],
        ]);
    }
}
