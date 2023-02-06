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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class DefinitionNotFoundException extends \RuntimeException
{
    public function __construct(string $className)
    {
        parent::__construct(sprintf('Swagger definition "%s" was not found', $className));
    }
}
