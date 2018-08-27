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

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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
