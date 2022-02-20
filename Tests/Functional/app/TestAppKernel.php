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
use Linkin\Bundle\SwaggerResolverBundle\LinkinSwaggerResolverBundle;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CartController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CustomerController;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\NelmioApiDocController\CustomerPasswordController;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class TestAppKernel extends Kernel
{
    use MicroKernelTrait;

    public const LOADER_NELMIO_API_DOC = 'NelmioApiDoc';
    public const LOADER_SWAGGER_PHP = 'SwaggerPhp';

    /**
     * @var string
     */
    private $testCase;

    /**
     * @var string
     */
    private $varDir;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Closure
     */
    private $closure;

    public function __construct(
        string $varDir,
        string $testCase,
        bool $disableSwaggerPhp,
        array $config,
        ?Closure $closure = null,
        string $environment = 'test',
        bool $debug = true
    ) {
        $this->testCase = $testCase;
        $this->varDir = $varDir;
        $this->config = $config;
        $this->closure = $closure;

        $this->copyLockFile($disableSwaggerPhp);

        parent::__construct($environment, $debug);
    }

    public function registerBundles(): array
    {
        $bundles = [
            new FrameworkBundle(),
            new LinkinSwaggerResolverBundle(),
        ];

        if (self::LOADER_NELMIO_API_DOC === $this->testCase) {
            $bundles[] = new NelmioApiDocBundle();
        }

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return parent::getProjectDir().'/Tests/Functional';
    }

    public function getRootDir(): string
    {
        return $this->getProjectDir();
    }

    public function getCacheDir(): string
    {
        return $this->varDir.'/cache/'.$this->testCase.'/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->varDir.'/logs/'.$this->testCase;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        if (self::LOADER_NELMIO_API_DOC === $this->testCase) {
            $routes->import($this->getProjectDir().'/src/NelmioApiDocController', '/api', 'annotation');

            return;
        }

        $routes->import($this->getProjectDir().'/app/routing.yaml');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->register('logger', NullLogger::class);

        if ($this->closure instanceof Closure) {
            \call_user_func($this->closure, $c);
        }

        $c->loadFromExtension('framework', [
            'secret' => 'test',
            'test' => null,
        ]);

        $c->loadFromExtension('linkin_swagger_resolver', array_merge([
            'path_merge_strategy' => ReplaceLastWinMergeStrategy::class,
            'swagger_php' => [
                'exclude' => [
                    '%kernel.project_dir%/src/NelmioApiDocController',
                ],
            ],
        ], $this->config));

        if (self::LOADER_NELMIO_API_DOC !== $this->testCase) {
            $c
                ->autowire('Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CartController')
                ->addTag('controller.service_arguments')
            ;
            $c->autowire(
                'Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerController'
            )->addTag('controller.service_arguments');
            $c->autowire(
                'Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController\CustomerPasswordController'
            )->addTag('controller.service_arguments');

            return;
        }

        $c->autowire(CartController::class)->addTag('controller.service_arguments');
        $c->autowire(CustomerController::class)->addTag('controller.service_arguments');
        $c->autowire(CustomerPasswordController::class)->addTag('controller.service_arguments');
        $c->loadFromExtension('nelmio_api_doc', [
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

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.test_case'] = $this->testCase;

        return $parameters;
    }

    private function copyLockFile(bool $disableSwaggerPhp): void
    {
        $rawData = file_get_contents(parent::getProjectDir().'/composer.lock');
        $fakeLockFile = $this->getProjectDir().'/composer.lock';

        if (false === $disableSwaggerPhp) {
            file_put_contents($fakeLockFile, $rawData);

            return;
        }

        $originData = json_decode($rawData, true);
        $newData = $originData;
        $newData['packages-dev'] = [];

        foreach ($originData['packages-dev'] as $package) {
            if ('zircote/swagger-php' === $package['name']) {
                continue;
            }

            $newData['packages-dev'][] = $package;
        }

        file_put_contents($fakeLockFile, json_encode($newData, \JSON_UNESCAPED_SLASHES));
    }
}
