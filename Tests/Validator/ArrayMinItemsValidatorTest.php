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
use Linkin\Bundle\SwaggerResolverBundle\Validator\ArrayMinItemsValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ArrayMinItemsValidatorTest extends TestCase
{
    private const TYPE_ARRAY = 'array';

    private const COLLECTION_FORMAT_CSV = 'csv';
    private const COLLECTION_FORMAT_MULTI = 'multi';

    /**
     * @var ArrayMinItemsValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new ArrayMinItemsValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $format, ?int $minItems, bool $expectedResult): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => $format,
            'minItems' => $minItems,
        ]);

        $isSupported = $this->sut->supports($schemaProperty);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'type' => '_invalid_format_',
                'minItems' => 3,
                'expectedResult' => false,
            ],
            'Fail when minItems was not set' => [
                'type' => self::TYPE_ARRAY,
                'minItems' => null,
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::TYPE_ARRAY,
                'minItems' => 3,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToPassValidationDataProvider
     */
    public function testFailToPassValidation(?string $collectionFormat, int $minItems, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => self::TYPE_ARRAY,
            'minItems' => $minItems,
            'collectionFormat' => $collectionFormat,
        ]);

        $this->expectException(InvalidOptionsException::class);

        $this->sut->validate($schemaProperty, 'days', $value);
    }

    public function failToPassValidationDataProvider(): array
    {
        return [
            'Fail when null collectionFormat and received array as string' => [
                'collectionFormat' => null,
                'minItems' => 3,
                'value' => 'monday,tuesday,wednesday',
            ],
            'Fail when set collectionFormat and received plain array' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'minItems' => 3,
                'value' => ['monday', 'tuesday', 'wednesday'],
            ],
            'Fail when unexpected delimiter' => [
                'collectionFormat' => '__delimiter__',
                'minItems' => 3,
                'value' => ['monday', 'tuesday',  'wednesday'],
            ],
            'Fail when invalid multi format' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'minItems' => 3,
                'value' => 'days-monday&days-tuesday&days-wednesday',
            ],
            'Fail when items lower than minimal count' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'minItems' => 3,
                'value' => 'days=monday&days=tuesday',
            ],
        ];
    }

    /**
     * @dataProvider canPassValidationDataProvider
     */
    public function testCanPassValidation(?string $collectionFormat, int $minItems, $value): void
    {
        $schemaProperty = SwaggerFactory::createSchemaProperty([
            'type' => self::TYPE_ARRAY,
            'minItems' => $minItems,
            'collectionFormat' => $collectionFormat,
        ]);

        $this->sut->validate($schemaProperty, 'days', $value);
        self::assertTrue(true);
    }

    public function canPassValidationDataProvider(): array
    {
        return [
            'Pass when null value' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'minItems' => 0,
                'value' => null,
            ],
            'Pass when null collectionFormat and received plain array' => [
                'collectionFormat' => null,
                'minItems' => 3,
                'value' => ['monday', 'tuesday',  'wednesday'],
            ],
            'Pass when CSV collectionFormat' => [
                'collectionFormat' => self::COLLECTION_FORMAT_CSV,
                'minItems' => 3,
                'value' => 'monday,tuesday,wednesday',
            ],
            'Pass when valid multi format and equal to minimal count' => [
                'collectionFormat' => self::COLLECTION_FORMAT_MULTI,
                'minItems' => 3,
                'value' => 'days=monday&days=tuesday&days=wednesday',
            ],
        ];
    }
}
