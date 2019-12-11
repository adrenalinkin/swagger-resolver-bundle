<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Enum;

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
