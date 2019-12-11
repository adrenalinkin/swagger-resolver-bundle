<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function in_array;
use function sprintf;

class NumberMinimumValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return in_array($property->getType(), [ParameterTypeEnum::NUMBER, ParameterTypeEnum::INTEGER], true)
            && null !== $property->getMinimum()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $message = sprintf('Property "%s" value should be', $propertyName);
        $minimum = $property->getMinimum();

        if ($property->isExclusiveMinimum() && $value <= $minimum) {
            throw new InvalidOptionsException(sprintf('%s strictly greater than %s', $message, $minimum));
        }

        if (!$property->isExclusiveMinimum() && $value < $minimum) {
            throw new InvalidOptionsException(sprintf('%s greater than or equal to %s', $message, $minimum));
        }
    }
}
