<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy;

class ReplaceFirstWinMergeStrategy extends AbstractMergeStrategy
{
    /**
     * {@inheritdoc}
     */
    public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired)
    {
        if (isset($this->parameters[$name])) {
            return;
        }

        if ($isRequired) {
            $this->required[$name] = $name;
        }

        $this->parameters[$name] = $data;
    }
}
