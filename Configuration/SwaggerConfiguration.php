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

namespace Linkin\Bundle\SwaggerResolverBundle\Configuration;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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
    public function getPathDefinition(string $routePath, string $method): Schema
    {
        return $this->configurationLoader->getSchemaOperationCollection()->getSchema($routePath, $method);
    }
}
