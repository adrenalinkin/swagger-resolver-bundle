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

use function end;
use function explode;
use EXSyst\Component\Swagger\Swagger;
use function get_declared_classes;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoader extends AbstractAnnotationConfigurationLoader
{
    /**
     * Instance of nelmio Api configuration generator.
     *
     * @var ApiDocGenerator
     */
    private $apiDocGenerator;

    public function __construct(OperationParameterMerger $merger, RouterInterface $router, ApiDocGenerator $generator)
    {
        parent::__construct($merger, $router);

        $this->apiDocGenerator = $generator;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadConfiguration(): Swagger
    {
        return $this->apiDocGenerator->generate();
    }

    /**
     * {@inheritdoc}
     */
    protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void
    {
        $definitionNames = [];

        foreach ($definitionCollection->getIterator() as $definitionName => $definition) {
            $definitionName = (string) $definitionName;
            $definitionNames[$definitionName] = $definitionName;
        }

        foreach (get_declared_classes() as $fullClassName) {
            $explodedClassName = explode('\\', $fullClassName);
            $className = (string) end($explodedClassName);

            if (!isset($definitionNames[$className])) {
                continue;
            }

            $definitionCollection->addSchemaResource($className, $this->getFileResource($fullClassName));
        }

        // TODO: Throw exception when class was never found
    }
}
