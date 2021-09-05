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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\ArrayUniqueItemsValidator;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ArrayUniqueItemsValidatorTest extends TestCase
{
    private const TYPE_ARRAY = 'array';

    /**
     * @var ArrayUniqueItemsValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ArrayUniqueItemsValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, ?bool $hasUniqueItems, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $format,
            'uniqueItems' => $hasUniqueItems,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'type' => '_invalid_format_',
                'hasUniqueItems' => true,
                'expectedResult' => false,
            ],
            'Fail when unique items was not set' => [
                'type' => self::TYPE_ARRAY,
                'hasUniqueItems' => false,
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::TYPE_ARRAY,
                'hasUniqueItems' => true,
                'expectedResult' => true,
            ],
        ];
    }
}
