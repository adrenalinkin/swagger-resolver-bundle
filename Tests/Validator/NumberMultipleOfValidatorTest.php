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
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMultipleOfValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMultipleOfValidatorTest extends TestCase
{
    private const TYPE_NUMBER = 'number';
    private const TYPE_INT = 'integer';

    /**
     * @var NumberMultipleOfValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberMultipleOfValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, $multipleOf, bool $expectedResult): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => $type,
            'multipleOf' => $multipleOf,
        ]);

        $isSupported = $this->sut->supports($schemaProperty);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'multipleOf' => 3,
                'expectedResult' => false,
            ],
            'Fail with empty multipleOf value' => [
                'type' => self::TYPE_INT,
                'multipleOf' => null,
                'expectedResult' => false,
            ],
            'Success with int' => [
                'type' => self::TYPE_INT,
                'multipleOf' => 3,
                'expectedResult' => true,
            ],
            'Success with float' => [
                'type' => self::TYPE_NUMBER,
                'multipleOf' => 3.7,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation($multipleOf, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => self::TYPE_INT,
            'multipleOf' => $multipleOf,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'age', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with not multiple of int value' => [
                'multipleOf' => 13,
                'value' => 9,
            ],
            'Fail with not multiple of int negative value' => [
                'multipleOf' => 13,
                'value' => -9,
            ],
            'Fail with int value as string' => [
                'multipleOf' => 13,
                'value' => '-9',
            ],
            'Fail with not multiple of float value' => [
                'multipleOf' => 13.3,
                'value' => 9.3,
            ],
            'Fail with not multiple of float negative value' => [
                'multipleOf' => 13.3,
                'value' => -9.3,
            ],
            'Fail with float value as string' => [
                'multipleOf' => 13.3,
                'value' => '-9.3',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation($multipleOf, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => self::TYPE_INT,
            'multipleOf' => $multipleOf,
        ]);

        $this->sut->validate($schemaProperty, 'age', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation with multiple of int value' => [
                'multipleOf' => 13,
                'value' => 26,
            ],
            'Pass validation with multiple of int negative value' => [
                'multipleOf' => 13,
                'value' => -26,
            ],
            'Pass validation with int value as string' => [
                'multipleOf' => 13,
                'value' => '-26',
            ],
            'Pass validation with multiple of float value' => [
                'multipleOf' => 13.3,
                'value' => 26.6,
            ],
            'Pass validation with multiple of float negative value' => [
                'multipleOf' => 13.3,
                'value' => -26.6,
            ],
            'Pass validation with float value as string' => [
                'multipleOf' => 13.3,
                'value' => '-26.6',
            ],
        ];
    }
}
