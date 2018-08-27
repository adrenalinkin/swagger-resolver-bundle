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

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMinimumValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return \in_array($property->getType(), ['number', 'integer'], true) && null !== $property->getMinimum();
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
