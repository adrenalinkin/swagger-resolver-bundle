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
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function gettype;
use function in_array;
use function is_numeric;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMaximumValidator implements SwaggerValidatorInterface
{
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        if (null === $propertySchema->getMaximum()) {
            return false;
        }

        return in_array($propertySchema->getType(), [ParameterTypeEnum::NUMBER, ParameterTypeEnum::INTEGER], true);
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (false === is_numeric($value)) {
            $message = sprintf('Property "%s" should be number "%s" received instead', $propertyName, gettype($value));

            throw new InvalidOptionsException($message);
        }

        $message = sprintf('Property "%s" value should be', $propertyName);
        $maximum = $propertySchema->getMaximum();

        if ($propertySchema->isExclusiveMaximum() && $value >= $maximum) {
            throw new InvalidOptionsException(sprintf('%s strictly lower than %s', $message, $maximum));
        }

        if (!$propertySchema->isExclusiveMaximum() && $value > $maximum) {
            throw new InvalidOptionsException(sprintf('%s lower than or equal to %s', $message, $maximum));
        }
    }
}
