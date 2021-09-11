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
use Linkin\Bundle\SwaggerResolverBundle\Validator\ArrayUniqueItemsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ArrayUniqueItemsValidatorTest extends TestCase
{
    private const TYPE_ARRAY = 'array';

    private const COLLECTION_FORMAT_CSV = 'csv';
    private const COLLECTION_FORMAT_MULTI = 'multi';

    /**
     * @var ArrayUniqueItemsValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ArrayUniqueItemsValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, ?bool $hasUniqueItems, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $format,
            'uniqueItems' => $hasUniqueItems,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'type' => '_invalid_format_',
                'hasUniqueItems' => true,
                'expectedResult' => false,
            ],
            'Fail when unique items was not set' => [
                'type' => self::TYPE_ARRAY,
                'hasUniqueItems' => false,
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::TYPE_ARRAY,
                'hasUniqueItems' => true,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(?string $collectionFormat, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE_ARRAY,
            'uniqueItems' => true,
            'collectionFormat' => $collectionFormat,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schema, 'days', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when null collectionFormat and received array as string' => [
                'collectionFormat' => null,
                'value' => 'monday,tuesday,wednesday',
            ],
            'Fail when set collectionFormat and received plain array' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'value' => ['monday', 'tuesday', 'wednesday'],
            ],
            'Fail when unexpected delimiter' => [
                'collectionFormat' => '__delimiter__',
                'value' => ['monday', 'tuesday',  'wednesday'],
            ],
            'Fail when invalid multi format' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'value' => 'days-monday&days-tuesday&days-wednesday',
            ],
            'Fail when not unique values in array' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'value' => 'days=monday&days=tuesday&days=wednesday&days=monday',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $collectionFormat, $value): void
    {
        $schema = new Schema([
            'type' => self::TYPE_ARRAY,
            'uniqueItems' => true,
            'collectionFormat' => $collectionFormat,
        ]);

        $this->sut->validate($schema, 'days', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'value' => null,
            ],
            'Pass when null collectionFormat and received plain array' => [
                'collectionFormat' => null,
                'value' => ['monday', 'tuesday',  'wednesday'],
            ],
            'Pass when CSV collectionFormat' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'value' => 'monday,tuesday,wednesday',
            ],
            'Pass when valid multi format with unique items' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'value' => 'days=monday&days=tuesday&days=wednesday',
            ],
        ];
    }
}
