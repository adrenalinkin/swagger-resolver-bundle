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

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use Symfony\Component\Config\Resource\FileResource;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractFileConfigurationLoader implements SwaggerConfigurationLoaderInterface
{
    /**
     * @var FileResource[]
     */
    private $resources;

    /**
     * @param string $pathToFile
     */
    public function __construct(string $pathToFile)
    {
        $this->resources[] = new FileResource($pathToFile);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileResources(string $definitionName): array
    {
        return $this->resources;
    }
}
