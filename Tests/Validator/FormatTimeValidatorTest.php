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
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatTimeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimeValidatorTest extends TestCase
{
    private const FORMAT_TIME = 'time';

    /**
     * @var FormatTimeValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatTimeValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, bool $expectedResult): void
    {
        $schema = new Schema([
            'format' => $format,
        ]);

        $isSupported = $this->sut->supports($schema);

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
                'type' => self::FORMAT_TIME,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(?string $pattern, $value): void
    {
        $schema = new Schema([
            'format' => self::FORMAT_TIME,
            'pattern' => $pattern,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'savedAtTime', $value);
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
                'value' => '100:05:55',
            ],
            'Fail when pattern set and value can NOT convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '10_05_45',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $pattern, $value): void
    {
        $schema = new Schema([
            'format' => self::FORMAT_TIME,
            'pattern' => $pattern,
        ]);

        $this->sut->validate($schema, 'savedAtTime', $value);
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
            'Pass when value with correct time pattern' => [
                'pattern' => null,
                'value' => '10:05:45',
            ],
            'Pass when pattern set and value can convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '10/05/45',
            ],
        ];
    }
}