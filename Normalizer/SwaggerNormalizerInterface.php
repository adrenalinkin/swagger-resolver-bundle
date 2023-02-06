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

namespace Linkin\Bundle\SwaggerResolverBundle\Normalizer;

use EXSyst\Component\Swagger\Schema;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
interface SwaggerNormalizerInterface
{
    /**
     * Check is this normalizer supports received property.
     */
    public function supports(Schema $propertySchema, string $propertyName, bool $isRequired, array $context = []): bool;

    /**
     * TODO: normalizer should not throw an error - better return value as is.
     *       https://github.com/adrenalinkin/swagger-resolver-bundle/issues/57.
     *
     * Returns closure for normalizing property
     */
    public function getNormalizer(Schema $propertySchema, string $propertyName, bool $isRequired): \Closure;
}
