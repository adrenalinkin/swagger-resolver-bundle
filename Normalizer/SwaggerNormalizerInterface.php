<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Normalizer;

use Closure;
use EXSyst\Component\Swagger\Schema;

interface SwaggerNormalizerInterface
{
    /**
     * Check is this normalizer supports received property
     *
     * @param Schema $propertySchema
     * @param string $propertyName
     * @param bool $isRequired
     * @param array $context
     *
     * @return bool
     */
    public function supports(Schema $propertySchema, string $propertyName, bool $isRequired, array $context = []): bool;

    /**
     * Returns closure for normalizing property
     *
     * @param Schema $propertySchema
     * @param string $propertyName
     * @param bool $isRequired
     *
     * @return Closure
     */
    public function getNormalizer(Schema $propertySchema, string $propertyName, bool $isRequired): Closure;
}
