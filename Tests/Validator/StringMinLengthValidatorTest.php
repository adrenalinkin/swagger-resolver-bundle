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
use Linkin\Bundle\SwaggerResolverBundle\Validator\StringMinLengthValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function str_repeat;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringMinLengthValidatorTest extends TestCase
{
    private const TYPE = 'string';

    /**
     * @var StringMinLengthValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new StringMinLengthValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, ?int $minLength, bool $expectedResult): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => $type,
            'minLength' => $minLength,
        ]);

        $isSupported = $this->sut->supports($schemaProperty);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'minLength' => 90,
                'expectedResult' => false,
            ],
            'Fail with empty minLength' => [
                'type' => self::TYPE,
                'minLength' => null,
                'expectedResult' => false,
            ],
            'Success' => [
                'type' => self::TYPE,
                'minLength' => 90,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(int $minLength, $value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => self::TYPE,
            'minLength' => $minLength,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'description', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with boolean lower than allowed' => [
                'maxLength' => 2,
                'value' => true,
            ],
            'Fail with integer lower than allowed' => [
                'maxLength' => 4,
                'value' => 110,
            ],
            'Fail with float lower than allowed' => [
                'maxLength' => 5,
                'value' => 1.11,
            ],
            'Fail with latin string lower than allowed' => [
                'minLength' => 5,
                'value' => str_repeat('w', 4),
            ],
            'Fail with cyrillic string lower than allowed' => [
                'minLength' => 5,
                'value' => str_repeat('я', 4),
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(int $minLength, $value): void
    {
        $schemaProperty = FixturesProvider::createSchemaProperty([
            'type' => self::TYPE,
            'minLength' => $minLength,
        ]);

        $this->sut->validate($schemaProperty, 'description', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation with null' => [
                'maxLength' => 1,
                'value' => null,
            ],
            'Pass with boolean equal to allowed' => [
                'maxLength' => 1,
                'value' => true,
            ],
            'Pass with integer equal to allowed' => [
                'maxLength' => 3,
                'value' => 110,
            ],
            'Pass with float equal to allowed' => [
                'maxLength' => 4,
                'value' => 1.11,
            ],
            'Pass validation with latin string equal to allowed' => [
                'minLength' => 10,
                'value' => str_repeat('w', 10),
            ],
            'Pass validation with cyrillic string equal to allowed' => [
                'minLength' => 10,
                'value' => str_repeat('я', 10),
            ],
            'Pass validation with latin string' => [
                'minLength' => 10,
                'value' => str_repeat('w', 11),
            ],
            'Pass validation with cyrillic string' => [
                'minLength' => 10,
                'value' => str_repeat('я', 11),
            ],
        ];
    }
}
