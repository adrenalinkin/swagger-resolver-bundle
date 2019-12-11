<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function mb_strlen;
use function sprintf;

class StringMaxLengthValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $property->getType() && null !== $property->getMaxLength();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        if (mb_strlen($value) > $property->getMaxLength()) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should have %s character or less',
                $propertyName,
                $property->getMaxLength()
            ));
        }
    }
}
