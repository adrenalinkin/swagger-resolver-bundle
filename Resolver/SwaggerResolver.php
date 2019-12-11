<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Resolver;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\SwaggerValidatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function get_class;

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
    private $validators;

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
    public function offsetGet($option)
    {
        $resolvedValue = parent::offsetGet($option);
        $property = $this->schema->getProperties()->get($option);

        foreach ($this->validators as $validator) {
            if ($validator->supports($property)) {
                $validator->validate($property, $option, $resolvedValue);
            }
        }

        return $resolvedValue;
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
     * Removes property validator
     *
     * @param string $className
     *
     * @return self
     */
    public function removeValidator(string $className): self
    {
        unset($this->validators[$className]);

        return $this;
    }
}
