<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function sprintf;

class NormalizationFailedException extends InvalidOptionsException
{
    /**
     * @param string $propertyName
     * @param string $value
     */
    public function __construct(string $propertyName, string $value)
    {
        parent::__construct(sprintf('Failed to normalize property "%s" with value "%s"', $propertyName, $value));
    }
}
