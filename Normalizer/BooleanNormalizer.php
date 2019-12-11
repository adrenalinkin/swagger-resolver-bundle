<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Normalizer;

use Closure;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Linkin\Bundle\SwaggerResolverBundle\Exception\NormalizationFailedException;
use Symfony\Component\OptionsResolver\Options;

class BooleanNormalizer implements SwaggerNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, string $propertyName, bool $isRequired, array $context = []): bool
    {
        return $propertySchema->getType() === ParameterTypeEnum::BOOLEAN;
    }

    /**
     * {@inheritdoc}
     */
    public function getNormalizer(Schema $propertySchema, string $propertyName, bool $isRequired): Closure
    {
        return function (Options $options, $value) use ($isRequired, $propertyName) {
            if ($value === 'true' || $value === '1' || $value === 1 || $value === true) {
                return true;
            }

            if ($value === 'false' || $value === '0' || $value === 0 || $value === false) {
                return false;
            }

            if (!$isRequired && $value === null) {
                return null;
            }

            throw new NormalizationFailedException($propertyName, (string) $value);
        };
    }
}
