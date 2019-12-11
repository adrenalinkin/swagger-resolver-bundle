<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Normalizer;

use Closure;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Linkin\Bundle\SwaggerResolverBundle\Exception\NormalizationFailedException;
use Symfony\Component\OptionsResolver\Options;
use function is_numeric;

class NumberNormalizer implements SwaggerNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, string $propertyName, bool $isRequired, array $context = []): bool
    {
        return $propertySchema->getType() === ParameterTypeEnum::NUMBER;
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizer(Schema $propertySchema, string $propertyName, bool $isRequired): Closure
    {
        return function (Options $options, $value) use ($isRequired, $propertyName) {
            if (is_numeric($value)) {
                return (float) $value;
            }

            if (!$isRequired && $value === null) {
                return null;
            }

            throw new NormalizationFailedException($propertyName, (string) $value);
        };
    }
}
