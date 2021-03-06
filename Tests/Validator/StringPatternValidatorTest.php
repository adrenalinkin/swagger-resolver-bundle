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
use Linkin\Bundle\SwaggerResolverBundle\Validator\StringPatternValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringPatternValidatorTest extends TestCase
{
    private const TYPE = 'string';

    /**
     * @var StringPatternValidator
     */
    private $sut;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->sut = new StringPatternValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, ?string $pattern, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $type,
            'pattern' => $pattern,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'pattern' => '\d',
                'expectedResult' => false,
            ],
            'Fail with empty pattern' => [
                'type' => self::TYPE,
                'pattern' => null,
                'expectedResult' => false,
            ],
            'Success' => [
                'type' => self::TYPE,
                'pattern' => '\d',
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(string $pattern, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE,
            'pattern' => $pattern,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'version', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail with null instead string' => [
                'pattern' => '^[\d]+\.[\d]+\.[\d]+$',
                'value' => null,
            ],
            'Fail with string not match pattern' => [
                'pattern' => '^[\d]+\.[\d]+\.[\d]+$',
                'value' => '1-2-3',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(string $pattern, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE,
            'pattern' => $pattern,
        ]);

        $this->sut->validate($schema, 'version', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass validation with string' => [
                'pattern' => '^[\d]+\.[\d]+\.[\d]+$',
                'value' => '1.2.3',
            ],
            'Pass validation with string wrapped in backslashes' => [
                'pattern' => '/^[\d]+\.[\d]+\.[\d]+$/',
                'value' => '1.2.3',
            ],
            'Pass validation with integer' => [
                'pattern' => '^[\d]+$',
                'value' => 100,
            ],
            'Pass validation with float' => [
                'pattern' => '^[\d]+\.[\d]+$',
                'value' => 100.1,
            ],
        ];
    }
}
