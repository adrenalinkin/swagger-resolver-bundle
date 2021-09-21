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
use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

use function sprintf;

/**
 * This validation based on rfc3339 {@see https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14}
 *
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatDateTimeValidator implements SwaggerValidatorInterface
{
    private const SUPPORTED_FORMAT = 'date-time';

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

        DateTime::createFromFormat(DATE_ATOM, $value);

        $errors = DateTime::getLastErrors();

        if ($errors !== false && 0 < $errors['error_count']) {
            $message = sprintf('Property "%s" contains invalid date-time format', $propertyName);

            throw new InvalidOptionsException($message);
        }
    }
}
