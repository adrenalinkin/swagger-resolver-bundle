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
