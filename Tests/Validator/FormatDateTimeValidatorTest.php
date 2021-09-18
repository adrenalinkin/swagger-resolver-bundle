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

use Linkin\Bundle\SwaggerResolverBundle\Tests\SwaggerFactory;
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatDateTimeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatDateTimeValidatorTest extends TestCase
{
    private const FORMAT_DATETIME = 'date-time';

    /**
     * @var FormatDateTimeValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatDateTimeValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, bool $expectedResult): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
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
                'type' => self::FORMAT_DATETIME,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation($value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'format' => self::FORMAT_DATETIME,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'createdAt', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when true value' => [true],
            'Fail when false value' => [false],
            'Fail when zero value' => ['0'],
            'Fail when incorrect datetime pattern - number' => ['2020-07-01 10:00:01'],
            'Fail when incorrect datetime pattern - more than 4 digit' => ['19999-01-01T10:00:00Z'],
            // PHP does not support spec fraction
            'Fail when received time spec fraction' => ['1937-01-01T12:00:27.87+03:00'],
            'Fail when received time spec fraction UTC' => ['1985-04-12T23:20:50.52Z'],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation($value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'format' => self::FORMAT_DATETIME,
        ]);

        $this->sut->validate($schemaProperty, 'createdAt', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        /** @see https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14 */
        return [
            'Pass when null value' => [null],
            'Pass when empty string value' => [''],
            'Pass when value with UTC' => ['1985-04-12T23:20:50Z'],
            'Pass when value with offset' => ['1996-12-19T16:39:57-08:00'],
            'Pass when value with UTC and leap second' => ['1990-12-31T23:59:60Z'],
            'Pass when value with offset and leap second' => ['1990-12-31T15:59:60-08:00'],
            'Pass when value with Netherlands time' => ['1937-01-01T12:00:27+03:00'],
        ];
    }
}
