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

namespace Linkin\Bundle\SwaggerResolverBundle\Enum;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ParameterTypeEnum
{
    public const ARRAY = 'array';
    public const BOOLEAN = 'boolean';
    public const FILE = 'file';
    public const INTEGER = 'integer';
    public const NUMBER = 'number';
    public const STRING = 'string';

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return [self::ARRAY, self::BOOLEAN, self::FILE, self::INTEGER, self::NUMBER, self::STRING];
    }
}
