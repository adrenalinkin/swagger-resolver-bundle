<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use function array_unique;
use EXSyst\Component\Swagger\Schema;
use function sprintf;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ArrayUniqueItemsValidator extends AbstractArrayValidator
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return parent::supports($propertySchema, $context) && true === $propertySchema->hasUniqueItems();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        $value = $this->convertValueToArray($propertyName, $value, $propertySchema->getCollectionFormat());

        $itemsUnique = array_unique($value);

        if (\count($itemsUnique) !== \count($value)) {
            throw new InvalidOptionsException(sprintf('Property "%s" should contains unique items', $propertyName));
        }
    }
}
