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
use Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler\SwaggerValidatorCompilerPass;
use Linkin\Bundle\SwaggerResolverBundle\Validator\ArrayMaxItemsValidator;
use Linkin\Bundle\SwaggerResolverBundle\Validator\NumberMaximumValidator;
use Linkin\Bundle\SwaggerResolverBundle\Validator\StringMaxLengthValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerValidatorCompilerPassTest extends TestCase
{
    /**
     * @var SwaggerValidatorCompilerPass
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SwaggerValidatorCompilerPass();
    }

    public function testCanSkipWhenRootServiceWasNotDefine(): void
    {
        $container = $this->getContainerBuilder(false);

        $service = new Definition(NumberMaximumValidator::class);
        $service->addTag('linkin_swagger_resolver.validator');
        $container->setDefinition(NumberMaximumValidator::class, $service);

        $container->compile();

        self::assertTrue($container->hasDefinition(NumberMaximumValidator::class));
        self::assertFalse($container->hasDefinition(SwaggerResolverBuilder::class));
    }

    public function testCanApplyCompilePass(): void
    {
        $container = $this->getContainerBuilder(true);

        $validator = new Definition(NumberMaximumValidator::class);
        $validator->addTag('linkin_swagger_resolver.validator');
        $container->setDefinition(NumberMaximumValidator::class, $validator);

        $container->compile();

        $mainDefinition = $container->getDefinition(SwaggerResolverBuilder::class);
        $validators = $mainDefinition->getArgument(1);

        self::assertCount(1, $validators);
        self::assertSame(NumberMaximumValidator::class, (string) $validators[0]);
    }

    public function testCanApplyCompilePassInRightOrder(): void
    {
        $container = $this->getContainerBuilder(true);

        $validator = new Definition(NumberMaximumValidator::class);
        $validator->addTag('linkin_swagger_resolver.validator', ['priority' => 100]);
        $container->setDefinition(NumberMaximumValidator::class, $validator);

        $validator = new Definition(StringMaxLengthValidator::class);
        $validator->addTag('linkin_swagger_resolver.validator', ['priority' => 150]);
        $container->setDefinition(StringMaxLengthValidator::class, $validator);

        $validator = new Definition(ArrayMaxItemsValidator::class);
        $validator->addTag('linkin_swagger_resolver.validator', ['priority' => 50]);
        $container->setDefinition(ArrayMaxItemsValidator::class, $validator);

        $container->compile();

        $mainDefinition = $container->getDefinition(SwaggerResolverBuilder::class);
        $validators = $mainDefinition->getArgument(1);

        self::assertCount(3, $validators);
        self::assertSame(ArrayMaxItemsValidator::class, (string) $validators[0]);
        self::assertSame(NumberMaximumValidator::class, (string) $validators[1]);
        self::assertSame(StringMaxLengthValidator::class, (string) $validators[2]);
    }

    private function getContainerBuilder(bool $withMainDefinition): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->addCompilerPass($this->sut);

        if (false === $withMainDefinition) {
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
