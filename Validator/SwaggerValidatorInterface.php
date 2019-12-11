<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

interface SwaggerValidatorInterface
{
    /**
     * Check is this validator supports received property
     *
     * @param Schema $propertySchema
     * @param array  $context
     *
     * @return bool
     */
    public function supports(Schema $propertySchema, array $context = []): bool;

    /**
     * Validate received property value according to property schema configuration
     *
     * @param Schema $propertySchema
     * @param string $propertyName
     * @param mixed  $value
     *
     * @throws InvalidOptionsException If the option doesn't fulfill the specified validation rules
     */
    public function validate(Schema $propertySchema, string $propertyName, $value): void;
}
