<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use RuntimeException;
use function sprintf;

class PathNotFoundException extends RuntimeException
{
    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('Swagger path "%s" was not found', $path));
    }
}
