<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Configuration;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;

class SwaggerConfiguration implements SwaggerConfigurationInterface
{
    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @param SwaggerConfigurationLoaderInterface $loader
     */
    public function __construct(SwaggerConfigurationLoaderInterface $loader)
    {
        $this->configurationLoader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $definitionName): Schema
    {
        return $this->configurationLoader->getSchemaDefinitionCollection()->getSchema($definitionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPathDefinition(string $routeName, string $method): Schema
    {
        return $this->configurationLoader->getSchemaOperationCollection()->getSchema($routeName, $method);
    }
}
