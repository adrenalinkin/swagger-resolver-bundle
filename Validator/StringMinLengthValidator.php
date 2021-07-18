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
use function is_string;
use function mb_strlen;
use function sprintf;

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
        if (false === is_string($value)) {
            $message = sprintf('Property "%s" should be string "%s" received instead', $propertyName, gettype($value));

            throw new InvalidOptionsException($message);
        }

        $minLength = $propertySchema->getMinLength();

        if (mb_strlen($value) < $minLength) {
            $message = sprintf('Property "%s" should have %s character or more', $propertyName, $minLength);

            throw new InvalidOptionsException($message);
        }
    }
}
