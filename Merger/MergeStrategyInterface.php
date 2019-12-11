<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Merger;

interface MergeStrategyInterface
{
    /**
     * Add parameter into collection
     *
     * @param string $parameterSource
     * @param string $name
     * @param array $data
     * @param bool $isRequired
     */
    public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired);

    /**
     * Returns list of collected parameters
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Returns list of names of the required parameters
     *
     * @return array
     */
    public function getRequired(): array;

    /**
     * Clean all collected data
     */
    public function clean();
}
