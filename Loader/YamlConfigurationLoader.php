<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationLoader extends AbstractFileConfigurationLoader
{
    /**
     * @var string
     */
    private $pathToFile;

    /**
     * @param OperationParameterMerger $parameterMerger
     * @param RouterInterface $router
     * @param string $pathToFile
     */
    public function __construct(OperationParameterMerger $parameterMerger, RouterInterface $router, string $pathToFile)
    {
        parent::__construct($parameterMerger, $router, $pathToFile);

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
