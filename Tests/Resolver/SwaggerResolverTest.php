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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Resolver;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Tests\SwaggerFactory;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverTest extends TestCase
{
    public function testCanClearValidators(): void
    {
        $fieldName = 'description';
        $schemaDefinition = SwaggerFactory::createSchemaDefinition([
            $fieldName => [
                'type' => ParameterTypeEnum::STRING,
            ]
        ]);

        $sut = new SwaggerResolver($schemaDefinition);

        $validatorMock = $this->createMock(SwaggerValidatorInterface::class);
        $sut->addValidator($validatorMock);

        self::assertCount(1, $sut->getValidators());

        $sut->clear();

        self::assertCount(0, $sut->getValidators());
    }

    public function testValidatorWillNotCallWhenOptionDoesNotExistInSchema(): void
    {
        $fieldNameMain = 'description';
        $fieldNameOther = 'otherProperty';
        $schemaDefinition = SwaggerFactory::createSchemaDefinition([
            $fieldNameMain => [
                'type' => ParameterTypeEnum::STRING,
            ]
        ]);

        $schemaProperty = $schemaDefinition->getProperties()->get($fieldNameMain);

        $validatorMock = $this->createValidatorMock($schemaProperty);
        $validatorMock->expects(self::never())->method('validate');

        $sut = new SwaggerResolver($schemaDefinition);
        $sut->addValidator($validatorMock);
        $sut->setDefined($fieldNameMain);
        $sut->setDefined($fieldNameOther);
        $sut->resolve([$fieldNameOther => 'any text']);
    }

    public function testValidatorWillBeCallWhenOptionExistInSchema(): void
    {
        $fieldName = 'description';

        $schemaDefinition = SwaggerFactory::createSchemaDefinition([
            $fieldName => [
                'type' => ParameterTypeEnum::STRING,
            ]
        ]);

        $schemaProperty = $schemaDefinition->getProperties()->get($fieldName);

        $validatorMock = $this->createValidatorMock($schemaProperty);
        $validatorMock->expects(self::once())->method('validate');

        $sut = new SwaggerResolver($schemaDefinition);
        $sut->addValidator($validatorMock);
        $sut->setDefined($fieldName);
        $sut->resolve([$fieldName => 'any text']);
    }

    /**
     * @return SwaggerValidatorInterface|MockObject
     */
    private function createValidatorMock(Schema $expectedSchemaProperty): SwaggerValidatorInterface
    {
        $validatorMock = $this->createMock(SwaggerValidatorInterface::class);
        $validatorMock
            ->expects(self::once())
            ->method('supports')
            ->willReturnCallback(
                static function(Schema $property) use ($expectedSchemaProperty) {
                    return $property->getTitle() === $expectedSchemaProperty->getTitle()
                        && $property->getType() === $expectedSchemaProperty->getType();
                }
            )
        ;

        return $validatorMock;
    }
}
