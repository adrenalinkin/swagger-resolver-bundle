<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use DateTime;
use Exception;

class FormatTimestampValidator extends AbstractFormatDateValidator
{
    /**
     * {@inheritdoc}
     */
    protected function createDateFromValue($value): DateTime
    {
        $date = DateTime::createFromFormat('U', $value);

        if ($date instanceof DateTime) {
            return $date;
        }

        throw new Exception('Invalid timestamp value');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedFormatName(): string
    {
        return 'timestamp';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultPattern(): string
    {
        return '^[\d]+$';
    }
}
