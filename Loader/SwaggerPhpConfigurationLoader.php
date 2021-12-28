<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Loader;

use EXSyst\Component\Swagger\Swagger;
use function json_decode;
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Swagger\Annotations\Swagger as SwaggerZircote;
use function Swagger\scan;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerPhpConfigurationLoader extends AbstractAnnotationConfigurationLoader
{
    /**
     * @var array
     */
    private $exclude;

    /**
     * @var array
     */
    private $scan;

    /**
     * @var SwaggerZircote
     */
    private $swaggerAnnotation;

    public function __construct(OperationParameterMerger $merger, RouterInterface $router, array $scan, array $exclude)
    {
        parent::__construct($merger, $router);

        $this->scan = $scan;
        $this->exclude = $exclude;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadConfiguration(): Swagger
    {
        $this->swaggerAnnotation = scan($this->scan, [
            'exclude' => $this->exclude,
        ]);

        return new Swagger(json_decode((string) $this->swaggerAnnotation, true));
    }

    protected function registerDefinitionResources(SchemaDefinitionCollection $definitionCollection): void
    {
        foreach ($this->swaggerAnnotation->definitions as $zircoteDefinition) {
            $definitionCollection->addSchemaResource(
                $zircoteDefinition->definition,
                new FileResource($zircoteDefinition->_context->filename)
            );
        }
    }
}
