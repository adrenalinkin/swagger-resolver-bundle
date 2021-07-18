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
use function preg_match;
use function sprintf;
use function trim;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringPatternValidator implements SwaggerValidatorInterface
{
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $propertySchema->getType() && null !== $propertySchema->getPattern();
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (false === is_string($value)) {
            $message = sprintf('Property "%s" should be string "%s" received instead', $propertyName, gettype($value));

            throw new InvalidOptionsException($message);
        }

        $pattern = sprintf('/%s/', trim($propertySchema->getPattern(), '/'));

        if (!preg_match($pattern, $value)) {
            $message = sprintf('Property "%s" should match the pattern "%s"', $propertyName, $pattern);

            throw new InvalidOptionsException($message);
        }
    }
}
