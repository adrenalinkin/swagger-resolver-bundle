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
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMinimumValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMinimumValidatorTest extends TestCase
{
    private const TYPE_NUMBER = 'number';
    private const TYPE_INT = 'integer';

    /**
     * @var NumberMinimumValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberMinimumValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, $minimum, bool $expectedResult): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => $type,
            'minimum' => $minimum,
        ]);

        $isSupported = $this->sut->supports($schemaProperty);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'minimum' => 100,
                'expectedResult' => false,
            ],
            'Fail with empty minimum value' => [
                'type' => self::TYPE_INT,
                'minimum' => null,
                'expectedResult' => false,
            ],
            'Success with int' => [
                'type' => self::TYPE_INT,
                'minimum' => 90,
                'expectedResult' => true,
            ],
            'Success with float' => [
                'type' => self::TYPE_NUMBER,
                'minimum' => 10.08,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(bool $isExclusiveMinimum, $minimum, $value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => self::TYPE_INT,
            'minimum' => $minimum,
            'exclusiveMinimum' => $isExclusiveMinimum,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'age', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with minimal int value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10,
                'value' => 10,
            ],
            'Fail with int lower than minimal value' => [
                'isExclusiveMinimum' => false,
                'minimum' => 10,
                'value' => 9,
            ],
            'Fail with negative minimal int value' => [
                'isExclusiveMinimum' => false,
                'minimum' => -10,
                'value' => -11,
            ],
            'Fail with int value as string' => [
                'isExclusiveMinimum' => false,
                'minimum' => -10,
                'value' => '-11',
            ],
            'Fail with minimal float value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10.01,
                'value' => 10.01,
            ],
            'Fail with float lower than minimal value' => [
                'isExclusiveMinimum' => false,
                'minimum' => 10.1,
                'value' => 10.0009,
            ],
            'Fail with negative minimal float value' => [
                'isExclusiveMinimum' => false,
                'minimum' => -10.1,
                'value' => -10.11,
            ],
            'Fail with float value as string' => [
                'isExclusiveMinimum' => false,
                'minimum' => -10.1,
                'value' => '-10.11',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(bool $isExclusiveMinimum, $minimum, $value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => self::TYPE_INT,
            'minimum' => $minimum,
            'exclusiveMinimum' => $isExclusiveMinimum,
        ]);

        $this->sut->validate($schemaProperty, 'age', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation null value' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10,
                'value' => null,
            ],
            'Pass validation with not numeric value' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10,
                'value' => 'some-string',
            ],
            'Pass validation with greater than minimal int value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10,
                'value' => 11,
            ],
            'Pass validation with equal to minimal int value' => [
                'isExclusiveMinimum' => false,
                'minimum' => 10,
                'value' => 10,
            ],
            'Pass validation with negative minimal int value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => -10,
                'value' => -9,
            ],
            'Pass validation with negative minimal int value' => [
                'isExclusiveMinimum' => false,
                'minimum' => -10,
                'value' => -10,
            ],
            'Pass validation with int value as string' => [
                'isExclusiveMinimum' => true,
                'minimum' => -10,
                'value' => '-9',
            ],
            'Pass validation with greater than minimal float value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => 10.002,
                'value' => 10.0021,
            ],
            'Pass validation with equal to minimal float value' => [
                'isExclusiveMinimum' => false,
                'minimum' => 10.002,
                'value' => 10.002,
            ],
            'Pass validation with negative minimal float value and exclusive mode' => [
                'isExclusiveMinimum' => true,
                'minimum' => -1.1,
                'value' => -1.0,
            ],
            'Pass validation with negative minimal float value' => [
                'isExclusiveMinimum' => false,
                'minimum' => -1.0002,
                'value' => -1.0002,
            ],
            'Pass validation with float value as string' => [
                'isExclusiveMinimum' => true,
                'minimum' => -1,
                'value' => '-0.999',
            ],
        ];
    }
}
