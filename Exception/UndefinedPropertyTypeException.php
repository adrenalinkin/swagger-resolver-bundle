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
