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

namespace Linkin\Bundle\SwaggerResolverBundle\Merger;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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
