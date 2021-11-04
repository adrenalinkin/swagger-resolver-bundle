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
use Symfony\Component\Routing\RouterInterface;

use function json_decode;
use function Swagger\scan;

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
     * @param OperationParameterMerger $merger
     * @param RouterInterface $router
     * @param array $scan
     * @param array $exclude
     */
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
        $swaggerAnnotation = scan($this->scan, [
            'exclude' => $this->exclude,
        ]);

        return new Swagger(json_decode((string) $swaggerAnnotation, true));
    }
}
