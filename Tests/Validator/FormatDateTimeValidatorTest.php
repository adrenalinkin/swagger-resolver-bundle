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
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatDateTimeValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatDateTimeValidatorTest extends TestCase
{
    private const FORMAT_DATETIME = 'datetime';

    /**
     * @var FormatDateTimeValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatDateTimeValidator();
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
                'type' => self::FORMAT_DATETIME,
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
            'format' => self::FORMAT_DATETIME,
            'pattern' => $pattern,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'createdAt', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when true value' => [
                'pattern' => null,
                'value' => true,
            ],
            'Fail when value with incorrect datetime pattern - number' => [
                'pattern' => null,
                'value' => '2020-07/01 10:00:01',
            ],
            'Fail when value with incorrect datetime pattern - more than 4 digit' => [
                'pattern' => null,
                'value' => '19999-01-01 10:00:00',
            ],
            'Fail when pattern set and value can NOT convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '2020_05_05_10_00_00',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $pattern, $value): void
    {
        $schema = new Schema([
            'format' => self::FORMAT_DATETIME,
            'pattern' => $pattern,
        ]);

        $this->sut->validate($schema, 'createdAt', $value);
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
                'value' => '2020-01-01 10:00:00',
            ],
            'Pass when pattern set and value can convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '2020/01/01 10:00:00',
            ],
        ];
    }
}
