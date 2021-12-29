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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolver extends OptionsResolver
{
    /**
     * Definition schema.
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
     *
     * @return mixed
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
     * Adds property validator.
     */
    public function addValidator(SwaggerValidatorInterface $validator): self
    {
        $className = \get_class($validator);

        $this->validators[$className] = $validator;

        return $this;
    }

    public function removeValidator(string $className): self
    {
        unset($this->validators[$className]);

        return $this;
    }

    public function removeValidatorByObject(SwaggerValidatorInterface $validator): self
    {
        return $this->removeValidator(\get_class($validator));
    }
}
