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
class NumberMultipleOfValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return \in_array($propertySchema->getType(), [ParameterTypeEnum::NUMBER, ParameterTypeEnum::INTEGER], true)
            && null !== $propertySchema->getMultipleOf()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (false === is_numeric($value)) {
            return;
        }

        $divisionResult = $value % $propertySchema->getMultipleOf();

        if (0 !== $divisionResult) {
            $message = sprintf(
                'Property "%s" should be a multiple of %s',
                $propertyName,
                $propertySchema->getMultipleOf()
            );

            throw new InvalidOptionsException($message);
        }
    }
}
