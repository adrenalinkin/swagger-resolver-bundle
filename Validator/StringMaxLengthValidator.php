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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringMaxLengthValidator implements SwaggerValidatorInterface
{
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $propertySchema->getType() && null !== $propertySchema->getMaxLength();
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (null === $value) {
            return;
        }

        $stringValue = (string) $value;
        $maxLength = $propertySchema->getMaxLength();

        if (mb_strlen($stringValue) > $maxLength) {
            $message = sprintf('Property "%s" should have %s character or less', $propertyName, $maxLength);

            throw new InvalidOptionsException($message);
        }
    }
}
