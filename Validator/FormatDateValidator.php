<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

class FormatDateValidator extends AbstractFormatDateValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedFormatName(): string
    {
        return 'date';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPattern(): string
    {
        return '^[\d]{4}-[\d]{2}-[\d]{2}$';
    }
}
