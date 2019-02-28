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
use function in_array;
use function is_int;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class NumberMultipleOfValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return in_array($property->getType(), [ParameterTypeEnum::NUMBER, ParameterTypeEnum::INTEGER], true)
            && null !== $property->getMultipleOf()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $divisionResult = $value / $property->getMultipleOf();

        if (!is_int($divisionResult)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should be an integer after division by %s',
                $propertyName,
                $property->getMultipleOf()
            ));
        }
    }
}
