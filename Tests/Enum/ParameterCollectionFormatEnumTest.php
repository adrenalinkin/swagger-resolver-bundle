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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Enum;

use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterCollectionFormatEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ParameterCollectionFormatEnumTest extends TestCase
{
    public function testGetAll(): void
    {
        self::assertSame(ParameterCollectionFormatEnum::getAll(), [
            'csv',
            'ssv',
            'tsv',
            'pipes',
            'multi',
        ]);
    }

    public function testGetDelimiter(): void
    {
        self::assertSame(',', ParameterCollectionFormatEnum::getDelimiter('csv'));
        self::assertSame(' ', ParameterCollectionFormatEnum::getDelimiter('ssv'));
        self::assertSame("\t", ParameterCollectionFormatEnum::getDelimiter('tsv'));
        self::assertSame('|', ParameterCollectionFormatEnum::getDelimiter('pipes'));
        self::assertSame('&', ParameterCollectionFormatEnum::getDelimiter('multi'));
    }

    public function testFailGetDelimiterByUnexpectedFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        ParameterCollectionFormatEnum::getDelimiter('_undefined_');
    }
}
