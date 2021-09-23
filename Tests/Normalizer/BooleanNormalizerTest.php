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
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\BooleanNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class BooleanNormalizerTest extends TestCase
{
    private const TYPE_BOOLEAN = 'boolean';

    /**
     * @var BooleanNormalizer
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanNormalizer();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        $fieldName = 'rememberMe';
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
                'type' => self::TYPE_BOOLEAN,
                'expectedResult' => true,
            ],
        ];
    }

    public function testFailToNormalize(): void
    {
        $fieldName = 'rememberMe';
        $isRequired = true;

        $schemaDefinition = $this->createSchemaDefinition($fieldName);
        $schemaProperty =  $schemaDefinition->getProperties()->get($fieldName);

        $closure = $this->sut->getNormalizer($schemaProperty, $fieldName, $isRequired);

        $resolver = new SwaggerResolver($schemaDefinition);
        $resolver->setDefined($fieldName);
        $resolver->setNormalizer($fieldName, $closure);

        $this->expectException(NormalizationFailedException::class);

        $resolver->resolve([$fieldName => 'not_bool']);
    }

    public function testCanNormalizeNullWhenNotRequired(): void
    {
        $fieldName = 'rememberMe';
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
    public function testCanNormalize($originValue, bool $expectedResult): void
    {
        $fieldName = 'rememberMe';
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
            'string true' => [
                'originValue' => 'true',
                'expectedResult' => true,
            ],
            'integer true' => [
                'originValue' => 1,
                'expectedResult' => true,
            ],
            'boolean true' => [
                'originValue' => true,
                'expectedResult' => true,
            ],
            'string false' => [
                'originValue' => 'false',
                'expectedResult' => false,
            ],
            'integer false' => [
                'originValue' => 0,
                'expectedResult' => false,
            ],
            'boolean false' => [
                'originValue' => false,
                'expectedResult' => false,
            ],
        ];
    }

    private function createSchemaDefinition(string $fieldName, string $type = self::TYPE_BOOLEAN): Schema
    {
        return FixturesProvider::createSchemaDefinition([
            $fieldName => [
                'type' => $type
            ]
        ]);
    }
}
