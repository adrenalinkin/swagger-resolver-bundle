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

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function sprintf;

/**
 * This validation based on rfc3339 {@see https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14}
 *
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimeValidator implements SwaggerValidatorInterface
{
    private const SUPPORTED_TYPE = 'time';
    private const PATTERN = '/^(\d{2}):(\d{2}):(\d{2})$/';

    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return self::SUPPORTED_TYPE === $propertySchema->getFormat();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $value = (string) $value;

        if (!preg_match(self::PATTERN, $value, $matches)) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid time format', $propertyName));
        }

        [, $hour, $minute, $second] = $matches;

        if ($hour < 0 || $hour > 23) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid hours value', $propertyName));
        }

        if ($minute < 0 || $minute > 59) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid minutes value', $propertyName));
        }

        if ($second < 0 || $second > 59) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid seconds value', $propertyName));
        }
    }
}
