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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NelmioApiDocConfigurationLoader implements SwaggerConfigurationLoaderInterface
{
    /**
     * Instance of nelmio Api configuration generator
     *
     * @var ApiDocGenerator
     */
    private $apiDocGenerator;

    /**
     * @param ApiDocGenerator $apiDocGenerator
     */
    public function __construct(ApiDocGenerator $apiDocGenerator)
    {
        $this->apiDocGenerator = $apiDocGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(): Swagger
    {
        $swagger = $this->apiDocGenerator->generate();

        return $swagger;
    }
}
