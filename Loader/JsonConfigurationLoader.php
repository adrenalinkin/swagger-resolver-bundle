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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class JsonConfigurationLoader extends AbstractFileConfigurationLoader
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
        parent::__construct($pathToFile);

        $this->pathToFile = $pathToFile;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(): Swagger
    {
        return Swagger::fromFile($this->pathToFile);
    }
}
