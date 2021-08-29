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
use Linkin\Bundle\SwaggerResolverBundle\Validator\FormatTimestampValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimestampValidatorTest extends TestCase
{
    private const FORMAT_TIMESTAMP = 'timestamp';

    /**
     * @var FormatTimestampValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new FormatTimestampValidator();
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
                'type' => self::FORMAT_TIMESTAMP,
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
            'format' => self::FORMAT_TIMESTAMP,
            'pattern' => $pattern,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'updatedAt', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when value with incorrect timestamp pattern' => [
                'pattern' => null,
                'value' => '2020-10-10',
            ],
            'Fail when pattern set and value can NOT convert into DateTime' => [
                'pattern' => 'any-string-here',
                'value' => '2020-10-10 00:00:00',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $pattern, $value): void
    {
        $schema = new Schema([
            'format' => self::FORMAT_TIMESTAMP,
            'pattern' => $pattern,
        ]);

        $this->sut->validate($schema, 'updatedAt', $value);
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
            'Pass when empty zero value' => [
                'pattern' => null,
                'value' => '0',
            ],
            'Pass when false value' => [
                'pattern' => null,
                'value' => false,
            ],
            'Fail when true value' => [
                'pattern' => null,
                'value' => true,
            ],
            'Pass when value with correct time pattern' => [
                'pattern' => null,
                'value' => '1629620000',
            ],
            'Pass when pattern set and value can convert into DateTime' => [
                'pattern' => 'any-string-here', // TODO: should check format
                'value' => '1620000000',
            ],
        ];
    }
}
