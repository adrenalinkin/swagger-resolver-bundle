<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use RuntimeException;
use function sprintf;

class UndefinedPropertyTypeException extends RuntimeException
{
    /**
     * @param string $definitionName
     * @param string $propertyName
     * @param string $type
     */
    public function __construct(string $definitionName, string $propertyName, string $type)
    {
        parent::__construct(sprintf(
            'Property "%s" of the swagger definition "%s" contains undefined type "%s"',
            $propertyName,
            $definitionName,
            $type
        ));
    }
}
