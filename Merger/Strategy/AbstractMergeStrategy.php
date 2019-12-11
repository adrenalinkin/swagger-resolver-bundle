<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy;

use Linkin\Bundle\SwaggerResolverBundle\Merger\MergeStrategyInterface;

abstract class AbstractMergeStrategy implements MergeStrategyInterface
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $required;

    /**
     * {@inheritdoc}
     */
    abstract public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired);

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequired(): array
    {
        return $this->required;
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->parameters = [];
        $this->required = [];
    }
}
