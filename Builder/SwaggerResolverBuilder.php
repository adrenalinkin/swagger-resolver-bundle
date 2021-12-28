<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Builder;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Linkin\Bundle\SwaggerResolverBundle\Exception\UndefinedPropertyTypeException;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\SwaggerNormalizerInterface;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverBuilder
{
    /**
     * @var array
     */
    private $normalizationLocations;

    /**
     * @var SwaggerNormalizerInterface[]
     */
    private $swaggerNormalizers;

    /**
     * @var SwaggerValidatorInterface[]
     */
    private $swaggerValidators;

    /**
     * @param SwaggerValidatorInterface[]  $swaggerValidators
     * @param SwaggerNormalizerInterface[] $swaggerNormalizers
     */
    public function __construct(array $swaggerValidators, array $swaggerNormalizers, array $normalizationLocations)
    {
        $this->normalizationLocations = $normalizationLocations;
        $this->swaggerNormalizers = $swaggerNormalizers;
        $this->swaggerValidators = $swaggerValidators;
    }

    /**
     * @throws UndefinedPropertyTypeException
     */
    public function build(Schema $definition, string $definitionName): SwaggerResolver
    {
        $swaggerResolver = new SwaggerResolver($definition);

        $requiredProperties = $definition->getRequired();

        if (\is_array($requiredProperties)) {
            $swaggerResolver->setRequired($requiredProperties);
        }

        $propertiesCount = $definition->getProperties()->getIterator()->count();

        if (0 === $propertiesCount) {
            return $swaggerResolver;
        }

        /** @var Schema $propertySchema */
        foreach ($definition->getProperties() as $name => $propertySchema) {
            $swaggerResolver->setDefined($name);

            $allowedTypes = $this->getAllowedTypes($propertySchema);

            if (null === $allowedTypes) {
                $propertyType = $propertySchema->getType() ?? '';

                throw new UndefinedPropertyTypeException($definitionName, $name, $propertyType);
            }

            if (!$swaggerResolver->isRequired($name)) {
                $allowedTypes[] = 'null';
            }

            $swaggerResolver->setAllowedTypes($name, $allowedTypes);
            $swaggerResolver = $this->addNormalization($swaggerResolver, $name, $propertySchema);

            if (null !== $propertySchema->getDefault()) {
                $swaggerResolver->setDefault($name, $propertySchema->getDefault());
            }

            if (!empty($propertySchema->getEnum())) {
                $swaggerResolver->setAllowedValues($name, (array) $propertySchema->getEnum());
            }
        }

        foreach ($this->swaggerValidators as $validator) {
            $swaggerResolver->addValidator($validator);
        }

        return $swaggerResolver;
    }

    private function addNormalization(SwaggerResolver $resolver, string $name, Schema $propertySchema): SwaggerResolver
    {
        /* @see \Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger parameter location in title */
        if (!\in_array($propertySchema->getTitle(), $this->normalizationLocations, true)) {
            return $resolver;
        }

        $isRequired = $resolver->isRequired($name);

        foreach ($this->swaggerNormalizers as $normalizer) {
            if (!$normalizer->supports($propertySchema, $name, $isRequired)) {
                continue;
            }

            $closure = $normalizer->getNormalizer($propertySchema, $name, $isRequired);

            return $resolver
                ->setNormalizer($name, $closure)
                ->addAllowedTypes($name, 'string')
            ;
        }

        return $resolver;
    }

    /**
     * @return array
     */
    private function getAllowedTypes(Schema $propertySchema): ?array
    {
        $propertyType = $propertySchema->getType();
        $allowedTypes = [];

        if (ParameterTypeEnum::STRING === $propertyType) {
            $allowedTypes[] = 'string';

            return $allowedTypes;
        }

        if (ParameterTypeEnum::INTEGER === $propertyType) {
            $allowedTypes[] = 'integer';
            $allowedTypes[] = 'int';

            return $allowedTypes;
        }

        if (ParameterTypeEnum::BOOLEAN === $propertyType) {
            $allowedTypes[] = 'boolean';
            $allowedTypes[] = 'bool';

            return $allowedTypes;
        }

        if (ParameterTypeEnum::NUMBER === $propertyType) {
            $allowedTypes[] = 'double';
            $allowedTypes[] = 'float';

            return $allowedTypes;
        }

        if (ParameterTypeEnum::ARRAY === $propertyType) {
            $allowedTypes[] = null === $propertySchema->getCollectionFormat() ? 'array' : 'string';

            return $allowedTypes;
        }

        if ('object' === $propertyType) {
            $allowedTypes[] = 'object';
            $allowedTypes[] = 'array';

            return $allowedTypes;
        }

        if (null === $propertyType && $propertySchema->getRef()) {
            $ref = $propertySchema->getRef();

            $allowedTypes[] = 'object';
            $allowedTypes[] = 'array';
            $allowedTypes[] = $ref;

            return $allowedTypes;
        }

        return null;
    }
}
