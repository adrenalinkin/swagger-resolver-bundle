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

use RuntimeException;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ParameterCollectionFormatEnum
{
    public const CSV = 'csv';
    public const SSV = 'ssv';
    public const TSV = 'tsv';
    public const PIPES = 'pipes';
    public const MULTI = 'multi';

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return [self::CSV, self::SSV, self::TSV, self::PIPES, self::MULTI];
    }

    /**
     * @param string $collectionFormat
     *
     * @return string
     */
    public static function getDelimiter(string $collectionFormat): string
    {
        switch ($collectionFormat) {
            case self::CSV:
                return ',';
            case self::SSV:
                return ' ';
            case self::TSV:
                return "\t";
            case self::PIPES:
                return '|';
            case self::MULTI:
                return '&';
            default:
                throw new RuntimeException(sprintf('Unexpected collection format: %s', $collectionFormat));
        }
    }
}
