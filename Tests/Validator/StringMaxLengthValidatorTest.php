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
use Linkin\Bundle\SwaggerResolverBundle\Validator\StringMaxLengthValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function str_repeat;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringMaxLengthValidatorTest extends TestCase
{
    private const TYPE = 'string';

    /**
     * @var StringMaxLengthValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new StringMaxLengthValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, ?int $maxLength, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $type,
            'maxLength' => $maxLength,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'maxLength' => 100,
                'expectedResult' => false,
            ],
            'Fail with empty maxLength' => [
                'type' => self::TYPE,
                'maxLength' => null,
                'expectedResult' => false,
            ],
            'Success' => [
                'type' => self::TYPE,
                'maxLength' => 100,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(int $maxLength, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE,
            'maxLength' => $maxLength,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'description', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with null instead string' => [
                'maxLength' => 10,
                'value' => null,
            ],
            'Fail with boolean instead string' => [
                'maxLength' => 10,
                'value' => true,
            ],
            'Fail with integer instead string' => [
                'maxLength' => 10,
                'value' => 110,
            ],
            'Fail with float instead string' => [
                'maxLength' => 10,
                'value' => 1.10,
            ],
            'Fail with latin string greater than allowed' => [
                'maxLength' => 10,
                'value' => str_repeat('w', 11),
            ],
            'Fail with cyrillic string greater than allowed' => [
                'maxLength' => 10,
                'value' => str_repeat('я', 11),
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(int $maxLength, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE,
            'maxLength' => $maxLength,
        ]);

        $this->sut->validate($schema, 'description', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation with latin string equal to allowed' => [
                'maxLength' => 10,
                'value' => str_repeat('w', 10),
            ],
            'Pass validation with cyrillic string equal to allowed' => [
                'maxLength' => 10,
                'value' => str_repeat('я', 10),
            ],
            'Pass validation with latin string' => [
                'maxLength' => 10,
                'value' => str_repeat('w', 9),
            ],
            'Pass validation with cyrillic string' => [
                'maxLength' => 10,
                'value' => str_repeat('я', 9),
            ],
        ];
    }
}
