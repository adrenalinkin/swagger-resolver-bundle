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
     * @param RouterInterface $router
     * @param ApiDocGenerator $apiDocGenerator
     */
    public function __construct(RouterInterface $router, ApiDocGenerator $apiDocGenerator)
    {
        parent::__construct($router);

        $this->apiDocGenerator = $apiDocGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(): Swagger
    {
        $swagger = $this->apiDocGenerator->generate();

        $this->registerResources($swagger);

        return $swagger;
    }
}
