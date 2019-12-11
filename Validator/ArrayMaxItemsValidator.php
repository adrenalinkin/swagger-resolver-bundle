<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function count;
use function sprintf;

class ArrayMaxItemsValidator extends AbstractArrayValidator
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $property, array $context = []): bool
    {
        return parent::supports($property, $context) && null !== $property->getMaxItems();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $property, string $propertyName, $value): void
    {
        $value = $this->convertValueToArray($propertyName, $value, $property->getCollectionFormat());

        if (count($value) > $property->getMaxItems()) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should have %s items or less',
                $propertyName,
                $property->getMaxItems()
            ));
        }
    }
}
