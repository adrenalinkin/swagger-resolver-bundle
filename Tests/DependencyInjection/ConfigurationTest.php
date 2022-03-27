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

namespace DependencyInjection;

use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Configuration;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceFirstWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    /**
     * @var Configuration
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new Configuration();
    }

    /**
     * @dataProvider failWhenIncorrectValuesDataProvider
     */
    public function testFailWhenIncorrectValues(array $config): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration($config);
    }

    public function failWhenIncorrectValuesDataProvider(): iterable
    {
        yield [[
            'enable_normalization' => ['__unexpected__'],
        ]];

        yield [[
            'path_merge_strategy' => '__unexpected__',
        ]];

        yield [[
            'path_merge_strategy' => null,
        ]];

        yield [[
            'configuration_loader_service' => '__unexpected__',
        ]];
    }

    public function testCanApplyDefault(): void
    {
        $expected = [
            'enable_normalization' => ['query', 'path', 'header'],
            'path_merge_strategy' => StrictMergeStrategy::class,
            'configuration_loader_service' => null,
            'configuration_file' => null,
            'swagger_php' => [
                'scan' => [],
                'exclude' => [],
            ],
        ];

        $result = $this->processConfiguration([]);

        self::assertSame($expected, $result);
    }

    public function testCanApplyConfiguration(): void
    {
        $config = [
            'enable_normalization' => ['query'],
            'path_merge_strategy' => ReplaceFirstWinMergeStrategy::class,
            'configuration_loader_service' => JsonConfigurationLoader::class,
            'configuration_file' => '%kernel.project_dir%/src/swagger.php',
            'swagger_php' => [
                'scan' => [
                    '%kernel.project_dir%/src',
                ],
                'exclude' => [
                    '%kernel.project_dir%/src/NelmioApiDocController',
                ],
            ],
        ];

        $result = $this->processConfiguration($config);

        self::assertSame($config, $result);
    }

    private function processConfiguration(array $config): array
    {
        return (new Processor())->processConfiguration($this->sut, [$config]);
    }
}
