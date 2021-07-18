<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Validator;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMaximumValidator;
use PHPUnit\Framework\TestCase;

class NumberMaximumValidatorTest extends TestCase
{
    private const TYPE_NUMBER = 'number';
    private const TYPE_INT = 'integer';

    /**
     * @var NumberMaximumValidator
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new NumberMaximumValidator();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, $maximum, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $type,
            'maximum' => $maximum,
        ]);

        $isSupported = $this->sut->supports($schema);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported type' => [
                'type' => '_invalid_type_',
                'maximum' => 100,
                'expectedResult' => false,
            ],
            'Fail with empty maximum value' => [
                'type' => self::TYPE_INT,
                'maximum' => null,
                'expectedResult' => false,
            ],
            'Success with int' => [
                'type' => self::TYPE_INT,
                'maximum' => 100,
                'expectedResult' => true,
            ],
            'Success with float' => [
                'type' => self::TYPE_NUMBER,
                'maximum' => 10.99,
                'expectedResult' => true,
            ],
        ];
    }
}
