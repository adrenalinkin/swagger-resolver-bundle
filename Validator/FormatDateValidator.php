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

use function preg_match;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatDateValidator implements SwaggerValidatorInterface
{
    private const SUPPORTED_FORMAT = 'date';
    private const PATTERN = '/^(\d{4})-(\d{2})-(\d{2})$/';

    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return self::SUPPORTED_FORMAT === $propertySchema->getFormat();
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        $value = (string) $value;

        if (!preg_match(self::PATTERN, $value, $matches)) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid date format', $propertyName));
        }

        [, $year, $month, $day] = $matches;

        if (!checkdate((int) $month, (int) $day, (int) $year)) {
            throw new InvalidOptionsException(sprintf('Property "%s" contains invalid date format', $propertyName));
        }
    }
}
