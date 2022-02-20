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
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractKernel extends Kernel
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    private $varDir;

    /**
     * @var Closure
     */
    private $closure;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(
        string $varDir,
        bool $disableSwaggerPhp,
        array $config,
        ?Closure $closure = null,
        string $environment = 'test',
        bool $debug = true
    ) {
        $this->varDir = $varDir;
        $this->config = $config;
        $this->closure = $closure;
        $this->prefix = str_replace('\\', '_', static::class);

        $this->copyLockFile($disableSwaggerPhp);

        parent::__construct($environment, $debug);
    }

    abstract protected function configureContainer(ContainerBuilder $container): void;

    abstract protected function configureRoutes(RouteCollectionBuilder $routes);

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new LinkinSwaggerResolverBundle(),
        ];
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
        return $this->varDir.'/cache/'.$this->prefix.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->varDir.'/logs/'.$this->prefix;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->register('logger', NullLogger::class);

            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => null,
                'router' => [
                    'resource' => 'kernel:loadRoutes',
                    'type' => 'service',
                ],
            ]);

            if ($this->closure instanceof Closure) {
                \call_user_func($this->closure, $container);
            }

            $this->configureContainer($container);

            $container->addObjectResource($this);
        });
    }

    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $routes = new RouteCollectionBuilder($loader);
        $this->configureRoutes($routes);

        return $routes->build();
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['router.options.matcher.cache_class'] = $this->prefix.'UrlMatcher';

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
