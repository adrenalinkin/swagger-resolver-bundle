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
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class YamlConfigurationLoader extends AbstractFileConfigurationLoader
{
    /**
     * @var string
     */
    private $pathToFile;

    /**
     * @param OperationParameterMerger $parameterMerger
     * @param string $pathToFile
     */
    public function __construct(OperationParameterMerger $parameterMerger, string $pathToFile)
    {
        parent::__construct($parameterMerger, $pathToFile);

        $this->pathToFile = $pathToFile;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadConfiguration(): Swagger
    {
        return new Swagger(Yaml::parseFile($this->pathToFile));
    }
}
