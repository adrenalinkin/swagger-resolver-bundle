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
use function json_decode;
use function Swagger\scan;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerPhpConfigurationLoader implements SwaggerConfigurationLoaderInterface
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
     * @param array $scan
     * @param array $exclude
     */
    public function __construct(array $scan, array $exclude)
    {
        $this->scan = $scan;
        $this->exclude = $exclude;
    }

    /**
     * {@inheritdoc}
     */
    public function loadConfiguration(): Swagger
    {
        $swaggerAnnotation = scan($this->scan, [
            'exclude' => $this->exclude,
        ]);

        $swagger = new Swagger(json_decode((string) $swaggerAnnotation, true));

        return $swagger;
    }
}
