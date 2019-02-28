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
use function in_array;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMaximumValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return in_array($property->getType(), ['number', 'integer'], true) && null !== $property->getMaximum();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $message = sprintf('Property "%s" value should be', $propertyName);
        $maximum = $property->getMaximum();

        if ($property->isExclusiveMaximum() && $value >= $maximum) {
            throw new InvalidOptionsException(sprintf('%s strictly lower than %s', $message, $maximum));
        }

        if (!$property->isExclusiveMaximum() && $value > $maximum) {
            throw new InvalidOptionsException(sprintf('%s lower than or equal to %s', $message, $maximum));
        }
    }
}
