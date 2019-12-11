<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use RuntimeException;
use function sprintf;

class OperationNotFoundException extends RuntimeException
{
    /**
     * @param string $path
     * @param string $method
     */
    public function __construct(string $path, string $method)
    {
        parent::__construct(sprintf('Swagger operation for path "%s" with "%s" was not found', $path, $method));
    }
}
