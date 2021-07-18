<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMaximumValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class NumberMaximumValidatorTest extends TestCase
{
    private const TYPE_NUMBER = 'number';
    private const TYPE_INT = 'integer';

    /**
     * @var NumberMaximumValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberMaximumValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, $maximum, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $type,
            'maximum' => $maximum,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'maximum' => 100,
                'expectedResult' => false,
            ],
            'Fail with empty maximum value' => [
                'type' => self::TYPE_INT,
                'maximum' => null,
                'expectedResult' => false,
            ],
            'Success with int' => [
                'type' => self::TYPE_INT,
                'maximum' => 100,
                'expectedResult' => true,
            ],
            'Success with float' => [
                'type' => self::TYPE_NUMBER,
                'maximum' => 10.99,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(bool $isExclusiveMaximum, $maximum, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE_INT,
            'maximum' => $maximum,
            'exclusiveMaximum' => $isExclusiveMaximum,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'age', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with null instead number' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10,
                'value' => null,
            ],
            'Fail with boolean instead number' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10,
                'value' => true,
            ],
            'Fail with string instead number' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10,
                'value' => 'some-string',
            ],
            'Fail with maximal int value and exclusive mode' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10,
                'value' => 10,
            ],
            'Fail with int more than maximal value' => [
                'isExclusiveMaximum' => false,
                'maximum' => 10,
                'value' => 11,
            ],
            'Fail with maximal float value and exclusive mode' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10.01,
                'value' => 10.01,
            ],
            'Fail with float more than maximal value' => [
                'isExclusiveMaximum' => false,
                'maximum' => 10.1,
                'value' => 10.1001,
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(bool $isExclusiveMaximum, $maximum, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE_INT,
            'maximum' => $maximum,
            'exclusiveMaximum' => $isExclusiveMaximum,
        ]);

        $this->sut->validate($schema, 'age', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation with lower than maximal int value and exclusive mode' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10,
                'value' => 9,
            ],
            'Pass validation with lower than maximal int value' => [
                'isExclusiveMaximum' => false,
                'maximum' => 10,
                'value' => 10,
            ],
            'Pass validation with lower than maximal float value and exclusive mode' => [
                'isExclusiveMaximum' => true,
                'maximum' => 10.002,
                'value' => 10.001,
            ],
            'Pass validation with lower than maximal float value' => [
                'isExclusiveMaximum' => false,
                'maximum' => 10.002,
                'value' => 10.002,
            ],
        ];
    }
}
