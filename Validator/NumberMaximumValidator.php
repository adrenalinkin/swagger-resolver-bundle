<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function in_array;
use function sprintf;

class NumberMaximumValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return in_array($property->getType(), [ParameterTypeEnum::NUMBER, ParameterTypeEnum::INTEGER], true)
            && null !== $property->getMaximum()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $message = sprintf('Property "%s" value should be', $propertyName);
        $maximum = $property->getMaximum();

        if ($property->isExclusiveMaximum() && $value >= $maximum) {
            throw new InvalidOptionsException(sprintf('%s strictly lower than %s', $message, $maximum));
        }

        if (!$property->isExclusiveMaximum() && $value > $maximum) {
            throw new InvalidOptionsException(sprintf('%s lower than or equal to %s', $message, $maximum));
        }
    }
}
