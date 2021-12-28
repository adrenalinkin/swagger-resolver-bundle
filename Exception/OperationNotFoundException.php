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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class OperationNotFoundException extends RuntimeException
{
    public function __construct(string $path, string $method)
    {
        parent::__construct(sprintf('Swagger operation for path "%s" with "%s" was not found', $path, $method));
    }
}
