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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
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

        $validatorQueue = new \SplPriorityQueue();

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
