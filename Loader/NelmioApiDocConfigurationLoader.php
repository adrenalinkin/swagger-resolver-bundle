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
use Linkin\Bundle\SwaggerResolverBundle\Collection\SchemaDefinitionCollection;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoader extends AbstractSwaggerConfigurationLoader
{
    /**
     * @var ApiDocGenerator
     */
    private $apiDocGenerator;

    /**
     * @var string
     */
    private $projectDir;

    public function __construct(
        OperationParameterMerger $merger,
        RouterInterface $router,
        ApiDocGenerator $generator,
        string $projectDir
    ) {
        parent::__construct($merger, $router);

        $this->apiDocGenerator = $generator;
        $this->projectDir = $projectDir;
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
        $classMap = $this->getClassMap();

        foreach ($definitionCollection->getIterator() as $definitionName => $definition) {
            $definitionCollection->addSchemaResource($definitionName, new FileResource($classMap[$definitionName]));
        }
    }

    private function getClassMap(): array
    {
        $finder = (new Finder())->files()->in($this->projectDir)->exclude('vendor')->name('*.php');
        $classMap = [];

        foreach ($finder as $file) {
            $name = (string) str_replace('.php', '', $file->getFilename());
            $classMap[$name] = $file->getPathname();
        }

        return $classMap;
    }
}
