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
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\IntegerNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class IntegerNormalizerTest extends TestCase
{
    private const TYPE_INTEGER = 'integer';

    /**
     * @var IntegerNormalizer
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new IntegerNormalizer();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        $fieldName = 'clickCount';
        $schema = $this->createSchema($fieldName, $type);

        $isSupported = $this->sut->supports($schema, $fieldName, true);

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
                'type' => self::TYPE_INTEGER,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider failToNormalizeDataProvider
     */
    public function testFailToNormalize($value): void
    {
        $fieldName = 'clickCount';
        $isRequired = true;

        $schema = new Schema([
            'properties' => $this->createSchema($fieldName),
        ]);

        $closure = $this->sut->getNormalizer($schema, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schema);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $this->expectException(NormalizationFailedException::class);

        $resolver->resolve([$fieldName => $value]);
    }

    public function failToNormalizeDataProvider(): array
    {
        return [
            ['not_int'],
            ['f1'],
            ['1f'],
            ['true'],
            [true],
        ];
    }

    public function testCanNormalizeNullWhenNotRequired(): void
    {
        $fieldName = 'clickCount';
        $isRequired = false;
        $originValue = null;

        $schema = new Schema([
            'properties' => $this->createSchema($fieldName),
        ]);

        $closure = $this->sut->getNormalizer($schema, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schema);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $result = $resolver->resolve([$fieldName => $originValue]);

        self::assertSame($result[$fieldName], $originValue);
    }

    /**
     * @dataProvider normalizationDataProvider
     */
    public function testCanNormalize($originValue, int $expectedResult): void
    {
        $fieldName = 'clickCount';
        $isRequired = true;
        $schema = new Schema([
            'properties' => $this->createSchema($fieldName),
        ]);

        $closure = $this->sut->getNormalizer($schema, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schema);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $result = $resolver->resolve([$fieldName => $originValue]);

        self::assertSame($result[$fieldName], $expectedResult);
    }

    public function normalizationDataProvider(): array
    {
        return [
            'int as string int' => [
                'originValue' => '100',
                'expectedResult' => 100,
            ],
            'int as int' => [
                'originValue' => 100,
                'expectedResult' => 100,
            ],
            'int as string float' => [
                'originValue' => '99.9',
                'expectedResult' => 99,
            ],
            'int as float' => [
                'originValue' => 90.9,
                'expectedResult' => 90,
            ],
        ];
    }

    private function createSchema(string $fieldName, string $type = self::TYPE_INTEGER): Schema
    {
        return new Schema([
            'type' => $type,
            'title' => $fieldName,
        ]);
    }
}
