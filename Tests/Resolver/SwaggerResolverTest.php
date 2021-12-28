<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Resolver;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverTest extends TestCase
{
    public function testCanClearValidators(): void
    {
        $sut = new SwaggerResolver(FixturesProvider::createSchemaDefinition([]));

        $sut->addValidator($this->createMock(SwaggerValidatorInterface::class));
        $sut->addValidator($this->createAnonymousValidator(ParameterTypeEnum::STRING));

        self::assertCount(2, $sut->getValidators());

        $sut->clear();

        self::assertCount(0, $sut->getValidators());
    }

    public function testValidatorWillNotCallWhenOptionDoesNotExistInSchema(): void
    {
        $fieldNameMain = 'description';
        $fieldNameOther = 'otherProperty';
        $schemaDefinition = FixturesProvider::createSchemaDefinition([
            $fieldNameMain => [
                'type' => ParameterTypeEnum::STRING,
            ],
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

        $schemaDefinition = FixturesProvider::createSchemaDefinition([
            $fieldName => [
                'type' => ParameterTypeEnum::STRING,
            ],
        ]);

        $schemaProperty = $schemaDefinition->getProperties()->get($fieldName);

        $validatorMock = $this->createValidatorMock($schemaProperty);
        $validatorMock->expects(self::once())->method('validate');

        $sut = new SwaggerResolver($schemaDefinition);
        $sut->addValidator($validatorMock);
        $sut->setDefined($fieldName);
        $sut->resolve([$fieldName => 'any text']);
    }

    public function testCanCallSeveralValidatorForOneProperty(): void
    {
        $fieldName = 'description';

        $schemaDefinition = FixturesProvider::createSchemaDefinition([
            $fieldName => [
                'type' => ParameterTypeEnum::STRING,
            ],
        ]);

        $schemaProperty = $schemaDefinition->getProperties()->get($fieldName);

        $validatorFirstMock = $this->createValidatorMock($schemaProperty);
        $validatorFirstMock->expects(self::once())->method('validate');

        $validatorAnonymous = $this->createAnonymousValidator(ParameterTypeEnum::STRING);

        $sut = new SwaggerResolver($schemaDefinition);
        $sut->addValidator($validatorFirstMock);
        $sut->addValidator($validatorAnonymous);
        $sut->setDefined($fieldName);

        $this->expectException(InvalidOptionsException::class);

        $sut->resolve([$fieldName => 'any text']);
    }

    public function testCanAddOnlyOneValidatorOfTheSameClass(): void
    {
        $validatorMock = $this->createMock(SwaggerValidatorInterface::class);
        $validatorAnonymous = $this->createAnonymousValidator(ParameterTypeEnum::STRING);

        $sut = new SwaggerResolver(FixturesProvider::createSchemaDefinition([]));
        $sut->addValidator($validatorMock);

        self::assertCount(1, $sut->getValidators());

        $sut->addValidator($validatorMock);

        self::assertCount(1, $sut->getValidators());

        $sut->addValidator($validatorAnonymous);

        self::assertCount(2, $sut->getValidators());
    }

    public function testCanRemoveValidator(): void
    {
        $validatorMock = $this->createMock(SwaggerValidatorInterface::class);
        $validatorAnonymous = $this->createAnonymousValidator(ParameterTypeEnum::BOOLEAN);

        $sut = new SwaggerResolver(FixturesProvider::createSchemaDefinition([]));
        $sut->addValidator($validatorMock);
        $sut->addValidator($validatorAnonymous);

        self::assertCount(2, $sut->getValidators());

        $sut->removeValidator(\get_class($validatorMock));

        self::assertCount(1, $sut->getValidators());

        $sut->removeValidator(\get_class($validatorAnonymous));

        self::assertCount(0, $sut->getValidators());
    }

    public function testCanRemoveValidatorByObject(): void
    {
        $validatorMock = $this->createMock(SwaggerValidatorInterface::class);
        $validatorAnonymous = $this->createAnonymousValidator(ParameterTypeEnum::BOOLEAN);

        $sut = new SwaggerResolver(FixturesProvider::createSchemaDefinition([]));
        $sut->addValidator($validatorMock);
        $sut->addValidator($validatorAnonymous);

        self::assertCount(2, $sut->getValidators());

        $sut->removeValidatorByObject($validatorMock);

        self::assertCount(1, $sut->getValidators());

        $sut->removeValidatorByObject($validatorAnonymous);

        self::assertCount(0, $sut->getValidators());
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
                static function (Schema $property) use ($expectedSchemaProperty) {
                    return $property->getTitle() === $expectedSchemaProperty->getTitle()
                        && $property->getType() === $expectedSchemaProperty->getType();
                }
            )
        ;

        return $validatorMock;
    }

    private function createAnonymousValidator(string $supportedType): SwaggerValidatorInterface
    {
        return new class($supportedType) implements SwaggerValidatorInterface {
            /**
             * @var string
             */
            private $supportedType;

            public function __construct(string $supportedType)
            {
                $this->supportedType = $supportedType;
            }

            public function supports(Schema $propertySchema, array $context = []): bool
            {
                return $propertySchema->getType() === $this->supportedType;
            }

            public function validate(Schema $propertySchema, string $propertyName, $value): void
            {
                throw new InvalidOptionsException();
            }
        };
    }
}
