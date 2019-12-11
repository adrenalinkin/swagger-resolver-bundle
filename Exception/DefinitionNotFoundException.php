<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use RuntimeException;
use function sprintf;

class DefinitionNotFoundException extends RuntimeException
{
    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct(sprintf('Swagger definition "%s" was not found', $className));
    }
}
