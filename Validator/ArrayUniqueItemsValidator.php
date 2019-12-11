<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function array_unique;
use function count;
use function sprintf;

class ArrayUniqueItemsValidator extends AbstractArrayValidator
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return parent::supports($property, $context) && true === $property->hasUniqueItems();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $value = $this->convertValueToArray($propertyName, $value, $property->getCollectionFormat());

        $itemsUnique = array_unique($value);

        if (count($itemsUnique) !== count($value)) {
            throw new InvalidOptionsException(sprintf('Property "%s" should contains unique items', $propertyName));
        }
    }
}
