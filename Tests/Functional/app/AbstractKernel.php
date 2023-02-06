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

use Linkin\Bundle\SwaggerResolverBundle\LinkinSwaggerResolverBundle;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractKernel extends Kernel
{
    /**
     * @var string
     */
    private $varDir;

    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $prefix;

    public function __construct(string $varDir, array $config, ?\Closure $closure, string $environment, bool $debug)
    {
        $this->varDir = $varDir;
        $this->config = $config;
        $this->closure = $closure;
        $this->prefix = str_replace('\\', '_', static::class);

        $this->copyLockFile();

        parent::__construct($environment, $debug);
    }

    abstract protected function configureContainer(ContainerBuilder $container): void;

    abstract protected function getRouterConfig(): array;

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

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->register('logger', NullLogger::class);

            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'test' => null,
                'router' => $this->getRouterConfig(),
            ]);

            $this->loadFromExtension($container, 'linkin_swagger_resolver', [
                'path_merge_strategy' => ReplaceLastWinMergeStrategy::class,
            ]);

            if ($this->closure instanceof \Closure) {
                \call_user_func($this->closure, $container);
            }

            $this->configureContainer($container);

            $container->addObjectResource($this);
        });
    }

    protected function loadFromExtension(ContainerBuilder $container, string $configKey, array $defaultConfig): void
    {
        $container->loadFromExtension($configKey, array_merge($defaultConfig, $this->config[$configKey] ?? []));
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['router.options.matcher.cache_class'] = $this->prefix.'UrlMatcher';

        return $parameters;
    }

    private function copyLockFile(): void
    {
        $rawData = file_get_contents(parent::getProjectDir().'/composer.lock');
        $fakeLockFile = $this->getProjectDir().'/composer.lock';

        file_put_contents($fakeLockFile, $rawData);
    }
}
