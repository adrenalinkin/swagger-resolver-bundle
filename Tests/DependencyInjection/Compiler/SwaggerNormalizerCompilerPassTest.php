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

namespace DependencyInjection\Compiler;

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerNormalizerCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\BooleanNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\IntegerNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\NumberNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerNormalizerCompilerPassTest extends TestCase
{
    /**
     * @var SwaggerNormalizerCompilerPass
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SwaggerNormalizerCompilerPass();
    }

    public function testCanSkipWhenRootServiceWasNotDefine(): void
    {
        $container = $this->getContainerBuilder(false);

        $service = new Definition(BooleanNormalizer::class);
        $service->addTag('linkin_swagger_resolver.normalizer');
        $container->setDefinition(BooleanNormalizer::class, $service);

        $container->compile();

        self::assertTrue($container->hasDefinition(BooleanNormalizer::class));
        self::assertFalse($container->hasDefinition(SwaggerResolverBuilder::class));
    }

    public function testCanApplyCompilePass(): void
    {
        $container = $this->getContainerBuilder(true);

        $normalizer = new Definition(BooleanNormalizer::class);
        $normalizer->addTag('linkin_swagger_resolver.normalizer');
        $container->setDefinition(BooleanNormalizer::class, $normalizer);

        $container->compile();

        $mainDefinition = $container->getDefinition(SwaggerResolverBuilder::class);
        $normalizers = $mainDefinition->getArgument(1);

        self::assertCount(1, $normalizers);
        self::assertSame(BooleanNormalizer::class, (string) $normalizers[0]);
    }

    public function testCanApplyCompilePassInRightOrder(): void
    {
        $container = $this->getContainerBuilder(true);

        $normalizer = new Definition(BooleanNormalizer::class);
        $normalizer->addTag('linkin_swagger_resolver.normalizer', ['priority' => 100]);
        $container->setDefinition(BooleanNormalizer::class, $normalizer);

        $normalizer = new Definition(IntegerNormalizer::class);
        $normalizer->addTag('linkin_swagger_resolver.normalizer', ['priority' => 150]);
        $container->setDefinition(IntegerNormalizer::class, $normalizer);

        $normalizer = new Definition(NumberNormalizer::class);
        $normalizer->addTag('linkin_swagger_resolver.normalizer', ['priority' => 50]);
        $container->setDefinition(NumberNormalizer::class, $normalizer);

        $container->compile();

        $mainDefinition = $container->getDefinition(SwaggerResolverBuilder::class);
        $normalizers = $mainDefinition->getArgument(1);

        self::assertCount(3, $normalizers);
        self::assertSame(NumberNormalizer::class, (string) $normalizers[0]);
        self::assertSame(BooleanNormalizer::class, (string) $normalizers[1]);
        self::assertSame(IntegerNormalizer::class, (string) $normalizers[2]);
    }

    private function getContainerBuilder(bool $withMainDefinition): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->addCompilerPass($this->sut);

        if ($withMainDefinition === false) {
            return $container;
        }

        $mainDefinition = new Definition(SwaggerResolverBuilder::class);
        $mainDefinition->addArgument([]);
        $mainDefinition->addArgument([]);
        $mainDefinition->addArgument(['query']);
        $container->setDefinition(SwaggerResolverBuilder::class, $mainDefinition);

        return $container;
    }
}
