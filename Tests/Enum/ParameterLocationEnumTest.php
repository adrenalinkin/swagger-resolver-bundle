<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Enum;

use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterLocationEnum;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ParameterLocationEnumTest extends TestCase
{
    public function testGetAll(): void
    {
        self::assertSame(ParameterLocationEnum::getAll(), [
            'body',
            'formData',
            'header',
            'path',
            'query',
        ]);
    }
}
