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

namespace Linkin\Bundle\SwaggerResolverBundle\Merger;

use EXSyst\Component\Swagger\Collections\Definitions;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use function array_flip;
use function end;
use function explode;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class PathParameterMerger
{
    /**
     * @var MergeStrategyInterface
     */
    private $defaultMergeStrategy;

    /**
     * @param MergeStrategyInterface $defaultMergeStrategy
     */
    public function __construct(MergeStrategyInterface $defaultMergeStrategy)
    {
        $this->defaultMergeStrategy = $defaultMergeStrategy;
    }

    /**
     * @param Path $swaggerPath
     * @param string $requestMethod
     * @param Definitions $definitions
     * @param MergeStrategyInterface|null $mergeStrategy
     *
     * @return Schema
     */
    public function merge(
        Path $swaggerPath,
        string $requestMethod,
        Definitions $definitions,
        ?MergeStrategyInterface $mergeStrategy = null
    ): Schema {
        if (!$mergeStrategy) {
            $mergeStrategy = $this->defaultMergeStrategy;
        }

        $swaggerOperation = $swaggerPath->getOperation($requestMethod);

        /** @var Parameter $parameter */
        foreach ($swaggerOperation->getParameters() as $parameter) {
            if ($parameter->getIn() !== 'body') {
                $mergeStrategy->addParameter(
                    $parameter->getIn(),
                    $parameter->getName(),
                    $parameter->toArray() + ['title' => $parameter->getIn()],
                    $parameter->getRequired() === true
                );

                continue;
            }

            $parameterSchema = $parameter->getSchema();

            $ref = $parameterSchema->getRef();

            // body as reference
            if ($ref) {
                $explodedName = explode('/', $ref);
                $definitionName = end($explodedName);

                $refDefinition = $definitions->get($definitionName);
                $required = $refDefinition->getRequired() ?? [];
                $required = array_flip($required);

                foreach ($refDefinition->getProperties() as $defName => $defItem) {
                    $mergeStrategy->addParameter(
                        $parameter->getIn(),
                        $defName,
                        $defItem->toArray() + ['title' => $parameter->getIn()],
                        isset($required[$defName])
                    );
                }

                continue;
            }

            // body as object
            if ($parameterSchema->getType() === 'object') {
                $required = $parameterSchema->getRequired() ?? [];
                $required = array_flip($required);

                foreach ($parameterSchema->getProperties() as $bodyItemName => $currentBodyItem) {
                    $mergeStrategy->addParameter(
                        $parameter->getIn(),
                        $bodyItemName,
                        $currentBodyItem->toArray() + ['title' => $parameter->getIn()],
                        isset($required[$bodyItemName])
                    );
                }

                continue;
            }

            // body as scalar
            $mergeStrategy->addParameter(
                $parameter->getIn(),
                $parameter->getName(),
                $parameterSchema->toArray() + ['title' => $parameter->getIn()],
                $parameter->getRequired() === true
            );
        }

        $mergedSchema = new Schema();
        $mergedSchema->merge([
            'type' => 'object',
            'properties' => $mergeStrategy->getParameters(),
            'required' => $mergeStrategy->getRequired(),
        ]);

        $mergeStrategy->clean();

        return $mergedSchema;
    }
}
