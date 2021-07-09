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
use function preg_match;
use function sprintf;
use function trim;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class StringPatternValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return ParameterTypeEnum::STRING === $property->getType() && null !== $property->getPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $pattern = sprintf('/%s/', trim($property->getPattern(), '/'));

        if (null === $value || !preg_match($pattern, (string) $value)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should match the pattern "%s"',
                $propertyName,
                $pattern
            ));
        }
    }
}
