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
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractKernel extends Kernel
{
    use MicroKernelTrait;

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

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['router.options.matcher.cache_class'] = $this->prefix.'UrlMatcher';

        return $parameters;
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->register('logger', NullLogger::class);

        $c->loadFromExtension('framework', [
            'secret' => 'test',
            'test' => null,
        ]);

        if ($this->closure instanceof Closure) {
            \call_user_func($this->closure, $c);
        }
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
