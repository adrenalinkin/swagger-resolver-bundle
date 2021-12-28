<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Validator;

use EXSyst\Component\Swagger\Schema;
use function is_numeric;
use function sprintf;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class FormatTimestampValidator implements SwaggerValidatorInterface
{
    private const SUPPORTED_TYPE = 'timestamp';

    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return self::SUPPORTED_TYPE === $propertySchema->getFormat();
    }

    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!is_numeric($value)) {
            $this->throwException($propertyName);
        }

        $value = (float) $value;

        if ($value < 0) {
            $this->throwException($propertyName);
        }
    }

    private function throwException(string $propertyName): void
    {
        $message = sprintf('Property "%s" contains invalid %s value', $propertyName, self::SUPPORTED_TYPE);

        throw new InvalidOptionsException($message);
    }
}
