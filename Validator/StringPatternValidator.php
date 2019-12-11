<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function preg_match;
use function sprintf;
use function trim;

class StringPatternValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $property->getType() && null !== $property->getPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $pattern = sprintf('/%s/', trim($property->getPattern(), '/'));

        if (!preg_match($pattern, $value)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should match the pattern "%s"',
                $propertyName,
                $pattern
            ));
        }
    }
}
