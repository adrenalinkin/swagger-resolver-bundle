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

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use DateTime;
use Exception;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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
