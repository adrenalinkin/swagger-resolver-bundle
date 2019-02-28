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

namespace Linkin\Bundle\SwaggerResolverBundle\Exception;

use RuntimeException;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NormalizationFailedException extends RuntimeException
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
