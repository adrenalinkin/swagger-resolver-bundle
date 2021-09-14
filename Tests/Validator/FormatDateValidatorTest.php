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
    public function testFailToPassValidation(?string $pattern, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'format' => self::FORMAT_DATE,
            'pattern' => $pattern,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'birthday', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when true value' => [
                'pattern' => null,
                'value' => true,
            ],
            'Fail when value with incorrect time pattern - number' => [
                'pattern' => null,
                'value' => '100',
            ],
            'Fail when value with incorrect time pattern - more than 2 digit' => [
                'pattern' => null,
                'value' => '19999-01-01',
            ],
            'Fail when pattern set and value can NOT convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '2020_05_05',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $pattern, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'format' => self::FORMAT_DATE,
            'pattern' => $pattern,
        ]);

        $this->sut->validate($schemaProperty, 'birthday', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [
                'pattern' => null,
                'value' => null,
            ],
            'Pass when empty string value' => [
                'pattern' => null,
                'value' => '',
            ],
            'Pass when empty zero value' => [ // TODO: should not pass validation
                'pattern' => null,
                'value' => '0',
            ],
            'Pass when false value' => [ // TODO: should not pass validation
                'pattern' => null,
                'value' => false,
            ],
            'Pass when value with correct date pattern' => [
                'pattern' => null,
                'value' => '2020-01-01',
            ],
            'Pass when pattern set and value can convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '2020/01/01',
            ],
        ];
    }
}
