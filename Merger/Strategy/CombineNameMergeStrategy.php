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

namespace Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class CombineNameMergeStrategy extends AbstractMergeStrategy
{
    public const DELIMITER = '_';

    /**
     * {@inheritdoc}
     */
    public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired): void
    {
        $name = $parameterSource.self::DELIMITER.$name;

        if ($isRequired) {
            $this->required[$name] = $name;
        }

        $this->parameters[$name] = $data;
    }
}
