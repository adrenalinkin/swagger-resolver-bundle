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

namespace Linkin\Bundle\SwaggerResolverBundle\Builder;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Exception\UndefinedPropertyTypeException;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;

class SwaggerResolverBuilder
{
    /**
     * @var SwaggerValidatorInterface[]
     */
    private $swaggerValidators;

    /**
     * @param SwaggerValidatorInterface[] $swaggerValidators
     */
    public function __construct(array $swaggerValidators)
    {
        $this->swaggerValidators = $swaggerValidators;
    }

    /**
     * @param Schema $definition
     * @param string $definitionName
     *
     * @return SwaggerResolver
     *
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

    /**
     * @param Schema $propertySchema
     *
     * @return array
     */
    private function getAllowedTypes(Schema $propertySchema): ?array
    {
        $propertyType = $propertySchema->getType();
        $allowedTypes = [];

        if ('string' === $propertyType) {
            $allowedTypes[] = 'string';

            return $allowedTypes;
        }

        if ('integer' === $propertyType) {
            $allowedTypes[] = 'integer';
            $allowedTypes[] = 'int';

            return $allowedTypes;
        }

        if ('boolean' === $propertyType) {
            $allowedTypes[] = 'boolean';
            $allowedTypes[] = 'bool';

            return $allowedTypes;
        }

        if ('number' === $propertyType) {
            $allowedTypes[] = 'double';
            $allowedTypes[] = 'float';

            return $allowedTypes;
        }

        if ('array' === $propertyType) {
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
