<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Enum;

use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ParameterTypeEnumTest extends TestCase
{
    public function testGetAll(): void
    {
        self::assertSame(ParameterTypeEnum::getAll(), [
            'array',
            'boolean',
            'file',
            'integer',
            'number',
            'string',
        ]);
    }
}
