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

use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatTimestampValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimestampValidatorTest extends TestCase
{
    private const FORMAT_TIMESTAMP = 'timestamp';

    /**
     * @var FormatTimestampValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatTimestampValidator();
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
                'type' => self::FORMAT_TIMESTAMP,
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
            'format' => self::FORMAT_TIMESTAMP,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'updatedAt', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when timestamp is not numeric' => ['2020-10-10 10:00:00'],
            'Fail when lower than zero' => ['-1'],
            'Fail when false value' => [false],
            'Fail when true value' => [true],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation($value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'format' => self::FORMAT_TIMESTAMP,
        ]);

        $this->sut->validate($schemaProperty, 'updatedAt', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [null],
            'Pass when empty string value' => [''],
            'Pass when empty zero value' => ['0'],
            'Pass when value with correct timestamp' => ['1629620000'],
        ];
    }
}
