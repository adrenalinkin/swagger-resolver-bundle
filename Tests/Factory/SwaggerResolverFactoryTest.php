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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Factory;

use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Configuration\SwaggerConfiguration;
use Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Models\CustomerFull;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverFactoryTest extends TestCase
{
    public function testCanCreateForRequest(): void
    {
        $request = Request::create('https://test.com/api/customers/12', 'PUT');
        $context = new RequestContext($request->getBasePath(), $request->getMethod());
        $sut = $this->createSut($context);

        $resolver = $sut->createForRequest($request);
        self::assertSame([
            'x-auth-token',
            'userId',
            'name',
            'roles',
            'password',
            'email',
        ], $resolver->getRequiredOptions());
    }

    /**
     * @dataProvider canCreateForDefinitionDataProvider
     */
    public function testCanCreateForDefinition(string $definition): void
    {
        $sut = $this->createSut(new RequestContext());
        $resolver = $sut->createForDefinition($definition);
        self::assertSame([
            'id',
            'name',
            'roles',
            'email',
            'isEmailConfirmed',
            'registeredAt',
        ], $resolver->getRequiredOptions());
    }

    public function canCreateForDefinitionDataProvider(): iterable
    {
        yield ['CustomerFull'];
        yield [CustomerFull::class];
    }

    private function createSut(RequestContext $context): SwaggerResolverFactory
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = FixturesProvider::createRouter($context);
        $loader = new JsonConfigurationLoader($parameterMerger, $router, FixturesProvider::PATH_TO_SWG_JSON);
        $configuration = new SwaggerConfiguration($loader);
        $builder = new SwaggerResolverBuilder([], [], []);

        return new SwaggerResolverFactory($builder, $configuration, $router);
    }
}
