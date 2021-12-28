<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use function mb_strlen;
use function sprintf;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringMinLengthValidator implements SwaggerValidatorInterface
{
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $propertySchema->getType() && null !== $propertySchema->getMinLength();
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (null === $value) {
            return;
        }

        $stringValue = (string) $value;
        $minLength = $propertySchema->getMinLength();

        if (mb_strlen($stringValue) < $minLength) {
            $message = sprintf('Property "%s" should have %s character or more', $propertyName, $minLength);

            throw new InvalidOptionsException($message);
        }
    }
}
