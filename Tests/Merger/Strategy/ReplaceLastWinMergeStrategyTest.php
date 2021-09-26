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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Merger\Strategy;

use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ReplaceLastWinMergeStrategyTest extends TestCase
{
    /**
     * @var ReplaceLastWinMergeStrategy
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ReplaceLastWinMergeStrategy();
    }

    public function testCanMergeWhenFirstRequiredAndClean(): void
    {
        $expectedRequired = ['name' => 'name', 'x-auth-token' => 'x-auth-token'];
        $expectedParameters = [
            'name' => ['type' => 'string'],
            'x-auth-token' => ['type' => 'string'],
            'page' => ['type' => 'integer'],
            'discount' => ['type' => 'float'],
            'rememberMe' =>  ['type' => 'boolean'],
        ];

        $this->sut->addParameter('path', 'name', ['type' => 'string'], true);
        $this->sut->addParameter('header', 'x-auth-token', ['type' => 'string'], true);
        $this->sut->addParameter('query', 'page', ['type' => 'integer'], false);
        $this->sut->addParameter('query', 'discount', ['type' => 'integer'], true);
        $this->sut->addParameter('formData', 'discount', ['type' => 'float'], false);
        $this->sut->addParameter('body', 'rememberMe', ['type' => 'boolean'], false);

        self::assertSame($expectedRequired, $this->sut->getRequired());
        self::assertSame($expectedParameters, $this->sut->getParameters());

        $this->sut->clean();

        self::assertSame([], $this->sut->getRequired());
        self::assertSame([], $this->sut->getParameters());
    }

    public function testCanMergeWhenFirstNotRequiredAndClean(): void
    {
        $expectedRequired = ['name' => 'name', 'x-auth-token' => 'x-auth-token', 'discount' => 'discount'];
        $expectedParameters = [
            'name' => ['type' => 'string'],
            'x-auth-token' => ['type' => 'string'],
            'page' => ['type' => 'integer'],
            'discount' => ['type' => 'float'],
            'rememberMe' =>  ['type' => 'boolean'],
        ];

        $this->sut->addParameter('path', 'name', ['type' => 'string'], true);
        $this->sut->addParameter('header', 'x-auth-token', ['type' => 'string'], true);
        $this->sut->addParameter('query', 'page', ['type' => 'integer'], false);
        $this->sut->addParameter('query', 'discount', ['type' => 'integer'], false);
        $this->sut->addParameter('formData', 'discount', ['type' => 'float'], true);
        $this->sut->addParameter('body', 'rememberMe', ['type' => 'boolean'], false);

        self::assertSame($expectedRequired, $this->sut->getRequired());
        self::assertSame($expectedParameters, $this->sut->getParameters());

        $this->sut->clean();

        self::assertSame([], $this->sut->getRequired());
        self::assertSame([], $this->sut->getParameters());
    }
}
