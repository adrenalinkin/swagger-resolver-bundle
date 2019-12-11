<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterCollectionFormatEnum;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterTypeEnum;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function explode;
use function is_array;
use function sprintf;

abstract class AbstractArrayValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return ParameterTypeEnum::ARRAY === $property->getType();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function validate(Schema $property, string $propertyName, $value): void;

    /**
     * @param string      $propertyName
     * @param mixed       $value
     * @param string|null $collectionFormat
     *
     * @return array
     */
    protected function convertValueToArray(string $propertyName, $value, ?string $collectionFormat): array
    {
        if (null === $value) {
            return [];
        }

        if (null === $collectionFormat) {
            if (is_array($value)) {
                return $value;
            }

            throw new InvalidOptionsException(sprintf('Property "%s" should contain valid json array', $propertyName));
        }

        if (is_array($value)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should contain valid "%s" string',
                $propertyName,
                $collectionFormat
            ));
        }

        $delimiter = ParameterCollectionFormatEnum::getDelimiter($collectionFormat);
        $arrayValue = explode($delimiter, $value);

        if (ParameterCollectionFormatEnum::MULTI === $delimiter) {
            foreach ($arrayValue as &$item) {
                $exploded = explode('=', $item);
                $item = $exploded[1];
            }
        }

        return $arrayValue;
    }
}
