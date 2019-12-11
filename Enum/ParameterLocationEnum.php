<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Enum;

class ParameterLocationEnum
{
    public const IN_BODY = 'body';
    public const IN_FORM_DATA = 'formData';
    public const IN_HEADER = 'header';
    public const IN_PATH = 'path';
    public const IN_QUERY = 'query';

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return [self::IN_BODY, self::IN_FORM_DATA, self::IN_HEADER, self::IN_PATH, self::IN_QUERY];
    }
}
