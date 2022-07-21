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

namespace Linkin\Bundle\SwaggerResolverBundle\CacheWarmer;

use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerConfigurationCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var SwaggerConfigurationInterface
     */
    private $configuration;

    public function __construct(SwaggerConfigurationInterface $configurationLoader)
    {
        $this->configuration = $configurationLoader;
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function warmUp($cacheDir): void
    {
        if ($this->configuration instanceof WarmableInterface) {
            $this->configuration->warmUp($cacheDir);
        }
    }
}
