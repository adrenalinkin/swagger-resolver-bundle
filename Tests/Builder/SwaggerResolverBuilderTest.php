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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Builder;

use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Builder\SwaggerResolverBuilder;
use Linkin\Bundle\SwaggerResolverBundle\Exception\UndefinedPropertyTypeException;
use Linkin\Bundle\SwaggerResolverBundle\Loader\JsonConfigurationLoader;
use Linkin\Bundle\SwaggerResolverBundle\Merger\OperationParameterMerger;
use Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\ReplaceLastWinMergeStrategy;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\BooleanNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Normalizer\IntegerNormalizer;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\FixturesProvider;
use Linkin\Bundle\SwaggerResolverBundle\Validator\StringMaxLengthValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

use function array_values;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerResolverBuilderTest extends TestCase
{
    /**
     * @var SwaggerResolverBuilder
     */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new SwaggerResolverBuilder([], [], []);
    }

    public function testCanBuildWhenSchemaHaveNotProperties(): void
    {
        $resolver = $this->sut->build(new Schema(), 'empty');

        self::assertCount(0, $resolver->getRequiredOptions());
        self::assertCount(0, $resolver->getDefinedOptions());
    }

    public function testFailBuildWhenSchemaContainsUndefined(): void
    {
        $definition = FixturesProvider::createSchemaDefinition(['field' => ['type' => '__UNDEFINED__']]);

        $this->expectException(UndefinedPropertyTypeException::class);
        $this->sut->build($definition, 'wrongType');
    }

    public function testCanApplyDefaultValueFromDefinition(): void
    {
        $defaultQuestion = 'what?';
        $definition = FixturesProvider::createSchemaDefinition([
            'question' => ['type' => 'string', 'default' => $defaultQuestion]
        ]);

        $resolver = $this->sut->build($definition, 'anyway');

        $result = $resolver->resolve([]);

        self::assertSame(['question' => $defaultQuestion], $result);

        $result = $resolver->resolve(['question' => 'how are you?']);

        self::assertSame(['question' => 'how are you?'], $result);
    }

    public function testCanApplyValidator(): void
    {
        $sut = new SwaggerResolverBuilder([new StringMaxLengthValidator()], [], []);

        $definition = FixturesProvider::createSchemaDefinition([
            'name' => ['type' => 'string', 'maxLength' => 5]
        ]);

        $resolver = $sut->build($definition, 'anyway');

        $result = $resolver->resolve(['name' => 'nik']);
        self::assertSame(['name' => 'nik'], $result);

        $this->expectException(InvalidOptionsException::class);
        $resolver->resolve(['name' => 'nikolas']);
    }

    /**
     * @dataProvider canApplyNormalizerDataProvider
     */
    public function testCanApplyNormalizer(array $normalizers, array $locations, bool $normalized): void
    {
        $sut = new SwaggerResolverBuilder([], $normalizers, $locations);
        $configurationLoader = $this->createConfigurationLoader();
        $definition = $configurationLoader->getSchemaOperationCollection()->getSchema('customers_update', 'put');

        $resolver = $sut->build($definition, 'no matter');

        $dataToResolve = [
            'x-auth-token' => 'token',
            'userId' => '100',
            'name' => 'Homer',
            'roles' => ['guest'],
            'password' => 'paSSword',
            'email' => 'homer@gmail.com',
        ];

        if ($normalized === false) {
            $this->expectException(InvalidOptionsException::class);
        }

        $result = $resolver->resolve($dataToResolve);

        self::assertSame(100, $result['userId']);
    }

    public function canApplyNormalizerDataProvider(): iterable
    {
        $forBoolean = new BooleanNormalizer();
        $forInteger = new IntegerNormalizer();

        yield 'normalizer not found' => [
            [$forBoolean], ['path'], false
        ];

        yield 'location should not normalized' => [
            [$forInteger], ['query'], false
        ];

        yield 'normalized successfully' => [
            [$forBoolean, $forInteger], ['path'], true
        ];
    }

    /**
     * @dataProvider canBuildCorrectResolverDataProvider
     */
    public function testCanBuildCorrectResolver(Schema $definition, bool $isPassed, array $data): void
    {
        $resolver = $this->sut->build($definition, 'no matter');

        self::assertSame(array_values($definition->getRequired()), $resolver->getRequiredOptions());
        self::assertCount($definition->getProperties()->getIterator()->count(), $resolver->getDefinedOptions());

        if ($isPassed === false) {
            $this->expectException(InvalidArgumentException::class);
        }

        $resolver->resolve($data);
    }

    public function canBuildCorrectResolverDataProvider(): iterable
    {
        $configurationLoader = $this->createConfigurationLoader();
        $schemaOperation = $configurationLoader->getSchemaOperationCollection()->getSchema('customers_update', 'put');

        $successDataFull = [
            'x-auth-token' => 'token',
            'userId' => 1,
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'password' => 'paSSword',
            'email' => 'homer@gmail.com',
            'birthday' => '1965-05-12',
            'happyHour' => '14:00',
            'discount' => 30,
        ];

        yield [$schemaOperation, 'isPassed' => true, 'data' => $successDataFull];

        $dataWithoutNotRequiredField = $successDataFull;
        $dataWithoutNotRequiredField['happyHour'] = null;
        yield [$schemaOperation, 'isPassed' => true, 'data' => $dataWithoutNotRequiredField];

        unset($dataWithoutNotRequiredField['happyHour']);
        yield [$schemaOperation, 'isPassed' => true, 'data' => $dataWithoutNotRequiredField];

        $dataWithoutRequiredField = $successDataFull;
        unset($dataWithoutRequiredField['email']);
        yield [$schemaOperation, 'isPassed' => false, 'data' => $dataWithoutRequiredField];

        $dataWithIncorrectType = $successDataFull;
        $dataWithIncorrectType['discount'] = '30';
        yield [$schemaOperation, 'isPassed' => false, 'data' => $dataWithIncorrectType];

        $dataWithIncorrectEnum = $successDataFull;
        $dataWithIncorrectEnum['role'] = '__UNDEFINED__';
        yield [$schemaOperation, 'isPassed' => false, 'data' => $dataWithIncorrectEnum];

        $schemaCustomerDefinition = $configurationLoader->getSchemaDefinitionCollection()->getSchema('CustomerFull');
        yield [$schemaCustomerDefinition, 'isPassed' => true, 'data' => [
            'id' => 1,
            'name' => 'Homer',
            'roles' => ['guest'],
            'email' => 'homer@gmail.com',
            'isEmailConfirmed' => true,
            'registeredAt' => '2000-10-11 19:57:31',
        ]];

        $schemaCartDefinition = $configurationLoader->getSchemaDefinitionCollection()->getSchema('Cart');
        yield [$schemaCartDefinition, 'isPassed' => true, 'data' => [
            'totalPrice' => 1002.4,
            'promo' => [
                'code' => 'd30',
                'captcha' => 'abc1234',
            ],
            'itemList' => [
                [
                    'vendorCode' => '100000000001',
                    'count' => 3,
                    'price' => 100.5
                ],
                [
                    'vendorCode' => '100000000002',
                    'count' => 1,
                    'price' => 700.9
                ]
            ],
            'lastAddedItem' => [
                'vendorCode' => '100000000002',
                'count' => 1,
                'price' => 700.9
            ],
        ]];
    }

    private function createConfigurationLoader(): JsonConfigurationLoader
    {
        $parameterMerger = new OperationParameterMerger(new ReplaceLastWinMergeStrategy());
        $router = new Router(new YamlFileLoader(new FileLocator(__DIR__ . '/../Fixtures')), 'routing.yaml');

        return new JsonConfigurationLoader($parameterMerger, $router, __DIR__ . '/../Fixtures/Json/customer.json');
    }
}
