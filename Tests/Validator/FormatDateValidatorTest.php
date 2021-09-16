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
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatDateValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatDateValidatorTest extends TestCase
{
    private const FORMAT_DATE = 'date';

    /**
     * @var FormatDateValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatDateValidator();
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
                'type' => self::FORMAT_DATE,
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
            'format' => self::FORMAT_DATE,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'birthday', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when true value' => [true],
            'Fail when false value' => [false],
            'Fail when zero value' => ['0'],
            'Fail when value with incorrect datetime' => ['100'],
            'Fail when value with incorrect datetime - year' => ['20199-01-01'],
            'Fail when value with incorrect datetime - month' => ['2019-13-01'],
            'Fail when value with incorrect datetime - day' => ['2019-01-32'],
            'Fail when value with incorrect datetime - day february' => ['2019-02-29'],
            'Fail when value with incorrect datetime - day february+' => ['2020-02-30'],
            'Fail when value with incorrect datetime - dat zero' => ['2019-02-00'],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation($value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'format' => self::FORMAT_DATE,
        ]);

        $this->sut->validate($schemaProperty, 'birthday', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [null],
            'Pass when empty string value' => [''],
            'Pass when value with correct date pattern' => ['2020-01-01'],
            'Pass when value with correct date pattern - february' => ['2029-02-28'],
            'Pass when value with correct date pattern - february+' => ['2020-02-29'],
        ];
    }
}
