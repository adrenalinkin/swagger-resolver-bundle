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
use EXSyst\Component\Swagger\Schema;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use function preg_match;
use function sprintf;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
abstract class AbstractFormatDateValidator implements SwaggerValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(Schema $propertySchema, array $context = []): bool
    {
        return $this->getSupportedFormatName() === $propertySchema->getFormat();
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Schema $propertySchema, string $propertyName, $value): void
    {
        if (empty($value)) {
            return;
        }

        $value = (string) $value;

        if (null === $propertySchema->getPattern()) {
            $this->validateDatePattern($propertyName, $value);
        }

        try {
            $this->createDateFromValue($value);
        } catch (Exception $e) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" contains invalid %s value',
                $propertyName,
                $this->getSupportedFormatName()
            ));
        }
    }

    /**
     * @return string
     */
    abstract protected function getDefaultPattern(): string;

    /**
     * @return string
     */
    abstract protected function getSupportedFormatName(): string;

    /**
     * @param mixed $value
     *
     * @return DateTime
     *
     * @throws Exception
     */
    protected function createDateFromValue($value): DateTime
    {
        return new DateTime($value);
    }

    /**
     * @param string $propertyName
     * @param mixed  $value
     */
    protected function validateDatePattern(string $propertyName, $value): void
    {
        $pattern = sprintf('/%s/', $this->getDefaultPattern());

        if (!preg_match($pattern, $value)) {
            throw new InvalidOptionsException(sprintf(
                'Property "%s" should match the pattern "%s". Set pattern explicitly to avoid this exception',
                $propertyName,
                $this->getDefaultPattern()
            ));
        }
    }
}
