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
use Nelmio\ApiDocBundle\ApiDocGenerator;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoader extends AbstractAnnotationConfigurationLoader
{
    /**
     * Instance of nelmio Api configuration generator
     *
     * @var ApiDocGenerator
     */
    private $apiDocGenerator;

    /**
     * @param OperationParameterMerger $merger
     * @param RouterInterface $router
     * @param ApiDocGenerator $generator
     */
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
}
