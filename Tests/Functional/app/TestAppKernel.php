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

use InvalidArgumentException;
use Linkin\Bundle\SwaggerResolverBundle\LinkinSwaggerResolverBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class TestAppKernel extends Kernel
{
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

    public function __construct(
        string $varDir,
        string $testCase,
        bool $disableSwaggerPhp,
        string $environment,
        bool $debug
    ) {
        if (!is_dir(__DIR__.'/'.$testCase)) {
            throw new InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }

        if (!file_exists(__DIR__.'/'.$testCase.'/config.yaml')) {
            throw new InvalidArgumentException('The root config "%s" does not exist.');
        }

        $this->testCase = $testCase;
        $this->varDir = $varDir;

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

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/'.$this->testCase.'/config.yaml');
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
