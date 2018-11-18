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

namespace Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler;

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerValidatorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $validatorQueue = new \SplPriorityQueue();

        foreach ($container->findTaggedServiceIds('linkin_swagger_resolver.validator') as $id => $attributes) {
            $validatorReference = new Reference($id);

            $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;

            $validatorQueue->insert($validatorReference, $priority);
        }

        $validators = iterator_to_array($validatorQueue);

        $this->registerSwaggerResolverBuilder($container, $validators);
        $this->registerSwaggerResolverFactory($container);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $validators
     */
    private function registerSwaggerResolverBuilder(ContainerBuilder $container, array $validators): void
    {
        if (!$container->hasDefinition(SwaggerResolverBuilder::class)) {
            return;
        }

        $container
            ->getDefinition(SwaggerResolverBuilder::class)
            ->replaceArgument(0, $validators)
        ;
    }

    /**
     * @param ContainerBuilder $container
     */
    private function registerSwaggerResolverFactory(ContainerBuilder $container): void
    {
        $configurationLoaderId = $container->getParameter('linkin_swagger_resolver.configuration_loader');
        $container->getParameterBag()->remove('linkin_swagger_resolver.configuration_loader');

        $swaggerFactoryDefinition = new Definition(SwaggerResolverFactory::class);
        $swaggerFactoryDefinition
            ->addArgument($container->findDefinition(SwaggerResolverBuilder::class))
            ->addArgument($container->findDefinition($configurationLoaderId))
            ->addArgument($container->findDefinition('router'))
        ;

        $container->setDefinition('linkin_swagger_resolver.factory', $swaggerFactoryDefinition);
        $container->setAlias(SwaggerResolverFactory::class, 'linkin_swagger_resolver.factory');
    }
}
