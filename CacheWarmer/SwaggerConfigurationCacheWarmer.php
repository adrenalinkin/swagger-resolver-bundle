<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\CacheWarmer;

use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;

class SwaggerConfigurationCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var SwaggerConfigurationInterface
     */
    private $configuration;

    /**
     * @param SwaggerConfigurationInterface $configurationLoader
     */
    public function __construct(SwaggerConfigurationInterface $configurationLoader)
    {
        $this->configuration = $configurationLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        if ($this->configuration instanceof WarmableInterface) {
            $this->configuration->warmUp($cacheDir);
        }
    }
}
