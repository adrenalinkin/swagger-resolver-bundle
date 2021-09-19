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

use function array_values;

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

        self::assertSame($expectedResult->getRequired(), array_values($mergedSchema->getRequired()));

        foreach ($expectedResult->getProperties() as $name => $expectedParameter) {
            self::assertSame($mergedSchemaProperties->get($name)->toArray(), $expectedParameter->toArray());
        }
    }

    public function mergeDataProvider(): iterable
    {
        $swagger = FixturesLoader::loadFromJson();
        $customerGet = $swagger->getPaths()->get('/customers')->getOperation('get');
        $expectedRequired = ['x-auth-token'];
        $expectedProperties = [
            'x-auth-token' => $this->getExpectedMergeResult($customerGet, 'x-auth-token', 'header'),
            'page' => $this->getExpectedMergeResult($customerGet, 'page', 'query'),
            'perPage' => $this->getExpectedMergeResult($customerGet, 'perPage', 'query'),
        ];

        yield 'Merge header and query' => [
            'parameters' => $customerGet,
            'definitions' => $swagger->getDefinitions(),
            'expectedResult' => SwaggerFactory::createSchemaDefinition($expectedProperties, $expectedRequired),
        ];
    }

    private function getExpectedMergeResult(Operation $operation, string $parameterName, string $parameterPlace): array
    {
        $result = $operation->getParameters()->get($parameterName, $parameterPlace)->toArray();

        $result['title'] = $result['in'];
        unset($result['in']);

        return $result;
    }
}
