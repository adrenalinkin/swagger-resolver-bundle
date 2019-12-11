<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

class FormatTimeValidator extends AbstractFormatDateValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedFormatName(): string
    {
        return 'time';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPattern(): string
    {
        return '^[\d]{2}:[\d]{2}:[\d]{2}$';
    }
}
