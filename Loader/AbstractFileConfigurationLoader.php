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

use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaOperationCollection;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractFileConfigurationLoader extends AbstractSwaggerConfigurationLoader
{
    /**
     * @var FileResource
     */
    private $fileResource;

    /**
     * @param OperationParameterMerger $parameterMerger
     * @param RouterInterface $router
     * @param string $pathToFile
     */
    public function __construct(OperationParameterMerger $parameterMerger, RouterInterface $router, string $pathToFile)
    {
        parent::__construct($parameterMerger, $router);

        $this->fileResource = new FileResource($pathToFile);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void
    {
        foreach ($definitionCollection->getIterator() as $definitionName => $schema) {
            $definitionCollection->addSchemaResource($definitionName, $this->fileResource);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerOperationResources(SchemaOperationCollection $operationCollection): void
    {
        foreach ($operationCollection->getIterator() as $routeName => $methodList) {
            $operationCollection->addSchemaResource($routeName, $this->fileResource);
        }
    }
}
