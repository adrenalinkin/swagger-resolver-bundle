<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMinimumValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

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
        $schema = new Schema([
            'type' => $type,
            'minimum' => $minimum,
        ]);

        $isSupported = $this->sut->supports($schema);

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
        $schema = new Schema([
            'type' => self::TYPE_INT,
            'minimum' => $minimum,
            'exclusiveMinimum' => $isExclusiveMinimum,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'age', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with null instead number' => [
                'isExclusiveMinimum' => true,
                'minimum' => 0,
                'value' => null,
            ],
            'Fail with boolean instead number' => [
                'isExclusiveMinimum' => true,
                'minimum' => 0,
                'value' => true,
            ],
            'Fail with string instead number' => [
                'isExclusiveMinimum' => true,
                'minimum' => 0,
                'value' => 'some-string',
            ],
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
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(bool $isExclusiveMinimum, $minimum, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE_INT,
            'minimum' => $minimum,
            'exclusiveMinimum' => $isExclusiveMinimum,
        ]);

        $this->sut->validate($schema, 'age', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
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
        ];
    }
}
