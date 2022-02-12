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

use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatTimeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimeValidatorTest extends TestCase
{
    private const FORMAT_TIME = 'time';

    /**
     * @var FormatTimeValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatTimeValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, bool $expectedResult): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'format' => $format,
        ]);

        $isSupported = $this->sut->supports($schemaProperty);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'format' => '_invalid_format_',
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::FORMAT_TIME,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation($value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'format' => self::FORMAT_TIME,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'savedAtTime', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when true value' => [true],
            'Fail when false value' => [false],
            'Fail when zero value' => [0],
            'Fail when value with incorrect format - number' => ['100'],
            'Fail when value with incorrect format - more than 2 digit' => ['100:05:55'],
            'Fail when value with incorrect hours' => ['24:00:00'],
            'Fail when value with incorrect minutes' => ['23:60:00'],
            'Fail when value with incorrect seconds' => ['23:59:60'],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation($value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'format' => self::FORMAT_TIME,
        ]);

        $this->sut->validate($schemaProperty, 'savedAtTime', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [null],
            'Pass when empty string value' => [''],
            'Pass when value with correct time' => ['01:02:03'],
            'Pass when value with correct time min' => ['00:00:00'],
            'Pass when value with correct time max' => ['23:59:59'],
        ];
    }
}
