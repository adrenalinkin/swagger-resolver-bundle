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
use RuntimeException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class TestAppKernel extends Kernel
{
    /**
     * @var string
     */
    private $testCase;

    public function __construct($testCase, $environment, $debug)
    {
        if (!is_dir(__DIR__.'/'.$testCase)) {
            throw new InvalidArgumentException(sprintf('The test case "%s" does not exist.', $testCase));
        }

        if (!file_exists(__DIR__.'/'.$testCase.'/config.yaml')) {
            throw new InvalidArgumentException('The root config "%s" does not exist.');
        }

        $this->testCase = $testCase;

        parent::__construct($environment, $debug);
    }

    public function registerBundles(): array
    {
        $filename = __DIR__.'/'.$this->testCase.'/bundles.php';

        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('The bundles file "%s" does not exist.', $filename));
        }

        return include $filename;
    }

    public function getProjectDir(): string
    {
        return parent::getProjectDir().'/Tests/';
    }

    public function getRootDir(): string
    {
        return $this->getProjectDir();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/cache/'.$this->testCase.'/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/logs/'.$this->testCase;
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
}
