<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Merger\Strategy;

use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StrictMergeStrategyTest extends TestCase
{
    /**
     * @var StrictMergeStrategy
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new StrictMergeStrategy();
    }

    public function testCanMergeAndClean(): void
    {
        $expectedRequired = ['name' => 'name', 'x-auth-token' => 'x-auth-token', 'rememberMe' => 'rememberMe'];
        $expectedParameters = [
            'name' => ['type' => 'string'],
            'x-auth-token' => ['type' => 'string'],
            'page' => ['type' => 'integer'],
            'discount' => ['type' => 'float'],
            'rememberMe' => ['type' => 'boolean'],
        ];

        $this->sut->addParameter('path', 'name', ['type' => 'string'], true);
        $this->sut->addParameter('header', 'x-auth-token', ['type' => 'string'], true);
        $this->sut->addParameter('query', 'page', ['type' => 'integer'], false);
        $this->sut->addParameter('formData', 'discount', ['type' => 'float'], false);
        $this->sut->addParameter('body', 'rememberMe', ['type' => 'boolean'], true);

        self::assertSame($expectedRequired, $this->sut->getRequired());
        self::assertSame($expectedParameters, $this->sut->getParameters());

        $this->sut->clean();

        self::assertSame([], $this->sut->getRequired());
        self::assertSame([], $this->sut->getParameters());
    }

    public function testFailToMergerParametersWithSameName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->addParameter('path', 'name', ['type' => 'string'], true);
        $this->sut->addParameter('query', 'name', ['type' => 'string'], false);
    }
}
