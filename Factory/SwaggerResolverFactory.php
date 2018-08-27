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

namespace Linkin\Bundle\SwaggerResolverBundle\Factory;

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Linkin\Bundle\SwaggerResolverBundle\Exception\DefinitionNotFoundException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Linkin\Bundle\SwaggerResolverBundle\Resolver\SwaggerResolver;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverFactory
{
    /**
     * @var Swagger
     */
    private $configuration;

    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $configurationLoader;

    /**
     * @var SwaggerValidatorInterface[]
     */
    private $swaggerValidators;

    /**
     * @param SwaggerValidatorInterface[]         $swaggerValidators
     * @param SwaggerConfigurationLoaderInterface $loader
     */
    public function __construct(array $swaggerValidators, SwaggerConfigurationLoaderInterface $loader)
    {
        $this->configurationLoader = $loader;
        $this->swaggerValidators = $swaggerValidators;
    }

    /**
     * @param string $definitionName
     *
     * @return SwaggerResolver
     */
    public function createForDefinition(string $definitionName): SwaggerResolver
    {
        $definition = $this->getDefinition($definitionName);
        $swaggerResolver = new SwaggerResolver($definition);

        $requiredProperties = $definition->getRequired();

        if (is_array($requiredProperties)) {
            $swaggerResolver->setRequired($requiredProperties);
        }

        $propertiesCount = $definition->getProperties()->getIterator()->count();

        if (0 === $propertiesCount) {
            return $swaggerResolver;
        }

        /** @var Schema $propertySchema */
        foreach ($definition->getProperties() as $name => $propertySchema) {
            $swaggerResolver->setDefined($name);

            $this->setAllowedType($swaggerResolver, $propertySchema, $name);

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
     * @param string $definitionName
     *
     * @return Schema
     */
    private function getDefinition(string $definitionName): Schema
    {
        if (null === $this->configuration) {
            $this->configuration = $this->configurationLoader->loadConfiguration();
        }

        $definitions = $this->configuration->getDefinitions();

        $explodedName = explode('\\', $definitionName);
        $definitionName = end($explodedName);

        if ($definitions->has($definitionName)) {
            return $definitions->get($definitionName);
        }

        throw new DefinitionNotFoundException($definitionName);
    }

    /**
     * @param SwaggerResolver $swaggerResolver
     * @param Schema          $propertySchema
     * @param string          $name
     */
    private function setAllowedType(SwaggerResolver $swaggerResolver, Schema $propertySchema, string $name): void
    {
        $allowedTypes = [];

        if ('array' === $propertySchema->getType()) {
            $allowedTypes[] = null === $propertySchema->getCollectionFormat() ? 'array' : 'string';
        } else {
            $allowedTypes[] = $propertySchema->getType();
        }

        if (!$swaggerResolver->isRequired($name)) {
            $allowedTypes[] = 'null';
        }

        $swaggerResolver->setAllowedTypes($name, $allowedTypes);
    }
}
