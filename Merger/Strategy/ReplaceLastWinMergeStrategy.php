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
class ReplaceLastWinMergeStrategy extends AbstractMergeStrategy
{
    /**
     * {@inheritdoc}
     */
    public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired)
    {
        if ($isRequired) {
            $this->required[$name] = $name;
        } else {
            unset($this->required[$name]);
        }

        $this->parameters[$name] = $data;
    }
}
