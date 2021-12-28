<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Merger\Strategy;

use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\CombineNameMergeStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class CombineNamMergeStrategyTest extends TestCase
{
    /**
     * @var CombineNameMergeStrategy
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new CombineNameMergeStrategy();
    }

    public function testCanMergeAndClean(): void
    {
        $expectedRequired = ['path_name' => 'path_name', 'query_discount' => 'query_discount'];
        $expectedParameters = [
            'path_name' => ['type' => 'string'],
            'header_x-auth-token' => ['type' => 'string'],
            'query_page' => ['type' => 'integer'],
            'query_discount' => ['type' => 'integer'],
            'formData_discount' => ['type' => 'float'],
            'body_rememberMe' => ['type' => 'boolean'],
        ];

        $this->sut->addParameter('path', 'name', ['type' => 'string'], true);
        $this->sut->addParameter('header', 'x-auth-token', ['type' => 'string'], false);
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
}
