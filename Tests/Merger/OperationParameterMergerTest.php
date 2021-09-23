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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Merger;

use EXSyst\Component\Swagger\Collections\Definitions;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesLoader;
use Linkin\Bundle\SwaggerResolverBundle\Tests\SwaggerFactory;
use PHPUnit\Framework\TestCase;

use function count;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class OperationParameterMergerTest extends TestCase
{
    /**
     * @var OperationParameterMerger
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
    }

    /**
     * @dataProvider mergeDataProvider
     */
    public function testMerge(Operation $operation, Definitions $definitions, Schema $expectedResult): void
    {
        $mergedSchema = $this->sut->merge($operation, $definitions);
        $mergedSchemaProperties = $mergedSchema->getProperties();

        self::assertCount(count($expectedResult->getRequired()), $mergedSchema->getRequired());

        foreach ($expectedResult->getRequired() as $expectedRequired) {
            self::assertArrayHasKey($expectedRequired, $mergedSchema->getRequired());
        }

        foreach ($expectedResult->getProperties() as $name => $expectedParameter) {
            self::assertSame($mergedSchemaProperties->get($name)->toArray(), $expectedParameter->toArray());
        }
    }

    public function mergeDataProvider(): iterable
    {
        $swagger = FixturesLoader::loadFromJson();

        $operation = $swagger->getPaths()->get('/customers')->getOperation('get');
        $requiredFields = ['x-auth-token'];
        $mergedProperties = [
            'x-auth-token' => $this->getOperationParameters($operation, 'x-auth-token', 'header'),
            'page' => $this->getOperationParameters($operation, 'page', 'query'),
            'perPage' => $this->getOperationParameters($operation, 'perPage', 'query'),
        ];

        yield 'get /customer - header+query' => [
            'parameters' => $operation,
            'definitions' => $swagger->getDefinitions(),
            'expectedResult' => SwaggerFactory::createSchemaDefinition($mergedProperties, $requiredFields),
        ];

        $operation = $swagger->getPaths()->get('/customers')->getOperation('post');
        $definition = $swagger->getDefinitions()->get('CustomerNew');
        $requiredFields = (array) $definition->getRequired();
        $requiredFields[] = 'x-auth-token';
        $mergedProperties = $this->getDefinitionProperties($definition);
        $mergedProperties['x-auth-token'] = $this->getOperationParameters($operation, 'x-auth-token', 'header');

        yield 'post /customer - header+query+body as reference' => [
            'parameters' => $operation,
            'definitions' => $swagger->getDefinitions(),
            'expectedResult' => SwaggerFactory::createSchemaDefinition($mergedProperties, $requiredFields),
        ];

        $operation = $swagger->getPaths()->get('/customers/{userId}')->getOperation('get');
        $requiredFields = ['x-auth-token', 'userId'];
        $mergedProperties = [
            'x-auth-token' => $this->getOperationParameters($operation, 'x-auth-token', 'header'),
            'userId' => $this->getOperationParameters($operation, 'userId', 'path'),
        ];

        yield 'get /customers/{userId} - header+path' => [
            'parameters' => $operation,
            'definitions' => $swagger->getDefinitions(),
            'expectedResult' => SwaggerFactory::createSchemaDefinition($mergedProperties, $requiredFields),
        ];
    }

    private function getDefinitionProperties(Schema $definition): array
    {
        $properties = $definition->getProperties()->toArray();
        $result = [];

        foreach ($properties as $name => $property) {
            $property['title'] = 'body';
            $result[$name] = $property;
        }

        return $result;
    }

    private function getOperationParameters(Operation $operation, string $name, string $place): array
    {
        $result = $operation->getParameters()->get($name, $place)->toArray();

        $result['title'] = $result['in'];
        unset($result['in']);

        return $result;
    }
}
