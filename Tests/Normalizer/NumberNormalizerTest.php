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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Normalizer;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\NormalizationFailedException;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\NumberNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Tests\SwaggerFactory;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberNormalizerTest extends TestCase
{
    private const TYPE_NUMBER = 'number';

    /**
     * @var NumberNormalizer
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberNormalizer();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        $fieldName = 'chance';
        $schemaDefinition = $this->createSchemaDefinition($fieldName, $type);
        $schemaProperty =  $schemaDefinition->getProperties()->get($fieldName);

        $isSupported = $this->sut->supports($schemaProperty, $fieldName, true);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'type' => '_invalid_format_',
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::TYPE_NUMBER,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToNormalizeDataProvider
     */
    public function testFailToNormalize($value): void
    {
        $fieldName = 'chance';
        $isRequired = true;

        $schemaDefinition = $this->createSchemaDefinition($fieldName);
        $schemaProperty =  $schemaDefinition->getProperties()->get($fieldName);

        $closure = $this->sut->getNormalizer($schemaProperty, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schemaDefinition);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $this->expectException(NormalizationFailedException::class);

        $resolver->resolve([$fieldName => $value]);
    }

    public function failToNormalizeDataProvider(): array
    {
        return [
            ['not_number'],
            ['f1.0'],
            ['1.0f'],
            ['true'],
            [true],
        ];
    }

    public function testCanNormalizeNullWhenNotRequired(): void
    {
        $fieldName = 'chance';
        $isRequired = false;
        $originValue = null;

        $schemaDefinition = $this->createSchemaDefinition($fieldName);
        $schemaProperty =  $schemaDefinition->getProperties()->get($fieldName);

        $closure = $this->sut->getNormalizer($schemaProperty, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schemaDefinition);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $result = $resolver->resolve([$fieldName => $originValue]);

        self::assertSame($result[$fieldName], $originValue);
    }

    /**
     * @dataProvider normalizationDataProvider
     */
    public function testCanNormalize($originValue, float $expectedResult): void
    {
        $fieldName = 'chance';
        $isRequired = true;
        $schemaDefinition = $this->createSchemaDefinition($fieldName);
        $schemaProperty =  $schemaDefinition->getProperties()->get($fieldName);

        $closure = $this->sut->getNormalizer($schemaProperty, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schemaDefinition);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $result = $resolver->resolve([$fieldName => $originValue]);

        self::assertSame($result[$fieldName], $expectedResult);
    }

    public function normalizationDataProvider(): array
    {
        return [
            'float as string int' => [
                'originValue' => '100',
                'expectedResult' => 100.0,
            ],
            'float as int' => [
                'originValue' => 100,
                'expectedResult' => 100.0,
            ],
            'float as string float' => [
                'originValue' => '99.9',
                'expectedResult' => 99.9,
            ],
            'float as float' => [
                'originValue' => 90.9,
                'expectedResult' => 90.9,
            ],
        ];
    }

    private function createSchemaDefinition(string $fieldName, string $type = self::TYPE_NUMBER): Schema
    {
        return SwaggerFactory::createSchemaDefinition([
            $fieldName => [
                'type' => $type
            ]
        ]);
    }
}
