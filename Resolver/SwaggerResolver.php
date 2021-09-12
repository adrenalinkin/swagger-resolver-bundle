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

namespace Linkin\Bundle\SwaggerResolverBundle\Resolver;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function get_class;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolver extends OptionsResolver
{
    /**
     * Definition schema
     *
     * @var Schema
     */
    private $schema;

    /**
     * A list of validators.
     *
     * @var SwaggerValidatorInterface[]
     */
    private $validators = [];

    /**
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): self
    {
         parent::clear();

         $this->validators = [];

         return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($option, bool $triggerDeprecation = true)
    {
        $resolvedValue = parent::offsetGet($option, $triggerDeprecation);
        $property = $this->schema->getProperties()->get($option);

        foreach ($this->validators as $validator) {
            if ($validator->supports($property)) {
                $validator->validate($property, $option, $resolvedValue);
            }
        }

        return $resolvedValue;
    }

    /**
     * @return SwaggerValidatorInterface[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Adds property validator
     *
     * @param SwaggerValidatorInterface $validator
     *
     * @return self
     */
    public function addValidator(SwaggerValidatorInterface $validator): self
    {
        $className = get_class($validator);

        $this->validators[$className] = $validator;

        return $this;
    }

    /**
     * @param SwaggerValidatorInterface $validator
     *
     * @return self
     */
    public function removeValidator(SwaggerValidatorInterface $validator): self
    {
        $className = get_class($validator);

        unset($this->validators[$className]);

        return $this;
    }
}
