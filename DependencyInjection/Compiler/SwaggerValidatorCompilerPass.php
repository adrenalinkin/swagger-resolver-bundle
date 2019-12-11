<?php

declare(strict_types=1);

namespace Linkin\Bundle\SwaggerResolverBundle\DependencyInjection\Compiler;

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use SplPriorityQueue;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function iterator_to_array;

class SwaggerValidatorCompilerPass implements CompilerPassInterface
{
    public const TAG = 'linkin_swagger_resolver.validator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SwaggerResolverBuilder::class)) {
            return;
        }

        $validatorQueue = new SplPriorityQueue();

        foreach ($container->findTaggedServiceIds(self::TAG) as $id => $attributes) {
            $validatorReference = new Reference($id);

            $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;

            $validatorQueue->insert($validatorReference, $priority);
        }

        $validators = iterator_to_array($validatorQueue);

        $container
            ->getDefinition(SwaggerResolverBuilder::class)
            ->replaceArgument(0, $validators)
        ;
    }
}
