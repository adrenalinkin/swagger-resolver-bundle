<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy;

use RuntimeException;
use function sprintf;

class StrictMergeStrategy extends AbstractMergeStrategy
{
    /**
     * {@inheritdoc}
     */
    public function addParameter(string $parameterSource, string $name, array $data, bool $isRequired)
    {
        if (isset($this->parameters[$name])) {
            throw new RuntimeException(sprintf(
                'Parameter "%s" has duplicate. Rename parameter or use another merger strategy',
                $name
            ));
        }

        if ($isRequired) {
            $this->required[$name] = $name;
        }

        $this->parameters[$name] = $data;
    }
}
