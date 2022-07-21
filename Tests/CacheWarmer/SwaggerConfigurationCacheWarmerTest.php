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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\CacheWarmer;

use Linkin\Bundle\SwaggerResolverBundle\CacheWarmer\SwaggerConfigurationCacheWarmer;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerCachedConfiguration;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfigurationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerConfigurationCacheWarmerTest extends TestCase
{
    public function testCanSkipWarmup(): void
    {
        /** @var SwaggerConfigurationInterface|MockObject $mock */
        $mock = $this->createMock(SwaggerConfigurationInterface::class);
        $sut = new SwaggerConfigurationCacheWarmer($mock);

        $sut->warmUp('some-string');
        self::assertTrue($sut->isOptional());
    }

    public function testCanWarmupWhenImplementWarmableInterface(): void
    {
        /** @var SwaggerCachedConfiguration|MockObject $mock */
        $mock = $this->createMock(SwaggerCachedConfiguration::class);
        $sut = new SwaggerConfigurationCacheWarmer($mock);

        $expectedCacheDir = 'some-string';

        $mock->expects(self::once())->method('warmUp')->with($expectedCacheDir);

        $sut->warmUp($expectedCacheDir);
        self::assertTrue($sut->isOptional());
    }
}
