<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

class FormatDateTimeValidator extends AbstractFormatDateValidator
{
    /**
     * {@inheritdoc}
     */
    protected function getSupportedFormatName(): string
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPattern(): string
    {
        return '^[\d]{4}-[\d]{2}-[\d]{2}[A-Z ]{1}[\d]{2}:[\d]{2}:[\d]{2}[A-Z]{0,1}$';
    }
}
