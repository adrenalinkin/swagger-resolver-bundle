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

use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class YamlConfigurationLoader implements SwaggerConfigurationLoaderInterface
{
    /**
     * @var string
     */
    private $pathToFile;

    /**
     * @param string $pathToFile
     */
    public function __construct(string $pathToFile)
    {
        $this->pathToFile = $pathToFile;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(): Swagger
    {
        return new Swagger(Yaml::parseFile($this->pathToFile));
    }
}
