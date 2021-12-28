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
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterCollectionFormatEnum;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractArrayValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return ParameterTypeEnum::ARRAY === $propertySchema->getType();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function validate(Schema $propertySchema, string $propertyName, $value): void;

    /**
     * TODO: Move into new ArrayNormalizer
     *       https://github.com/adrenalinkin/swagger-resolver-bundle/issues/55.
     *
     * @param mixed $value
     */
    protected function convertValueToArray(string $propertyName, $value, ?string $collectionFormat): array
    {
        if (null === $value) {
            return [];
        }

        if (null === $collectionFormat) {
            if (\is_array($value)) {
                return $value;
            }

            throw new InvalidOptionsException(sprintf('Property "%s" should contain valid json array', $propertyName));
        }

        if (\is_array($value)) {
            $message = sprintf('Property "%s" should contain valid "%s" string', $propertyName, $collectionFormat);

            throw new InvalidOptionsException($message);
        }

        $delimiter = ParameterCollectionFormatEnum::getDelimiter($collectionFormat);
        $arrayValue = explode($delimiter, $value);

        if (ParameterCollectionFormatEnum::MULTI !== $collectionFormat) {
            return $arrayValue;
        }

        return $this->convertMultiFormatToArray($propertyName, $arrayValue);
    }

    private function convertMultiFormatToArray(string $propertyName, array $arrayValue): array
    {
        $result = [];

        foreach ($arrayValue as $item) {
            $exploded = (array) explode('=', $item);
            $itemValue = $exploded[1] ?? null;

            if (null !== $itemValue) {
                $result[] = $itemValue;

                continue;
            }

            $message = sprintf(
                'Property "%s" should contains valid string with "%s" format like "key=value1&key=value2"',
                $propertyName,
                ParameterCollectionFormatEnum::MULTI
            );

            throw new InvalidOptionsException($message);
        }

        return $result;
    }
}
