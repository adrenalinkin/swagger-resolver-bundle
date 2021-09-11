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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Normalizer;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\BooleanNormalizer;
use PHPUnit\Framework\TestCase;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class BooleanNormalizerTest extends TestCase
{
    private const TYPE_BOOLEAN = 'boolean';

    /**
     * @var BooleanNormalizer
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new BooleanNormalizer();
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        $schema = new Schema([
            'type' => $type,
        ]);

        $isSupported = $this->sut->supports($schema, 'rememberMe', true);

        self::assertSame($isSupported, $expectedResult);
    }

    public function supportsDataProvider(): array
    {
        return [
            'Fail with unsupported format' => [
                'type' => '_invalid_format_',
                'expectedResult' => false,
            ],
            'Success with right format' => [
                'type' => self::TYPE_BOOLEAN,
                'expectedResult' => true,
            ],
        ];
    }
}
