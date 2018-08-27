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
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractArrayValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return 'array' === $property->getType();
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
            if (\is_array($value)) {
                return $value;
            }

            throw new InvalidOptionsException(sprintf('Property "%s" should contain valid json array', $propertyName));
        }

        if (\is_array($value)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should contain valid "%s" string',
                $propertyName,
                $collectionFormat
            ));
        }

        $delimiter = $this->getDelimiter($collectionFormat);
        $arrayValue = explode($delimiter, $value);

        if ('multi' === $delimiter) {
            foreach ($arrayValue as &$item) {
                $exploded = explode('=', $item);
                $item = $exploded[1];
            }
        }

        return $arrayValue;
    }

    /**
     * @param string $collectionFormat
     *
     * @return string
     */
    private function getDelimiter(string $collectionFormat): string
    {
        switch ($collectionFormat) {
            case 'csv':
                return ',';
            case 'ssv':
                return ' ';
            case 'tsv':
                return "\t";
            case 'pipes':
                return '|';
            case 'multi':
                return '&';
            default:
                throw new InvalidOptionsException(sprintf('Unexpected collection format: %s', $collectionFormat));
        }
    }
}
