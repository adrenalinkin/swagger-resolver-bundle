<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Merger;

use function array_flip;
use function end;
use function explode;
use EXSyst\Component\Swagger\Collections\Definitions;
use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Parameter;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Enum\ParameterLocationEnum;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class OperationParameterMerger
{
    /**
     * @var MergeStrategyInterface
     */
    private $mergeStrategy;

    public function __construct(MergeStrategyInterface $defaultMergeStrategy)
    {
        $this->mergeStrategy = $defaultMergeStrategy;
    }

    public function merge(Operation $swaggerOperation, Definitions $definitions): Schema
    {
        /** @var Parameter $parameter */
        foreach ($swaggerOperation->getParameters() as $parameter) {
            if (ParameterLocationEnum::IN_BODY !== $parameter->getIn()) {
                $this->mergeStrategy->addParameter(
                    $parameter->getIn(),
                    $parameter->getName(),
                    $parameter->toArray() + ['title' => $parameter->getIn()],
                    true === $parameter->getRequired()
                );

                continue;
            }

            $parameterSchema = $parameter->getSchema();

            $ref = $parameterSchema->getRef();

            // body as reference
            if (\is_string($ref)) {
                $explodedName = explode('/', $ref);
                $definitionName = end($explodedName);

                $refDefinition = $definitions->get($definitionName);
                $required = $refDefinition->getRequired() ?? [];
                $required = array_flip($required);

                foreach ($refDefinition->getProperties() as $defName => $defItem) {
                    $this->mergeStrategy->addParameter(
                        $parameter->getIn(),
                        $defName,
                        $defItem->toArray() + ['title' => $parameter->getIn()],
                        isset($required[$defName])
                    );
                }

                continue;
            }

            // body as object
            if ('object' === $parameterSchema->getType()) {
                $required = $parameterSchema->getRequired() ?? [];
                $required = array_flip($required);

                foreach ($parameterSchema->getProperties() as $bodyItemName => $currentBodyItem) {
                    $this->mergeStrategy->addParameter(
                        $parameter->getIn(),
                        $bodyItemName,
                        $currentBodyItem->toArray() + ['title' => $parameter->getIn()],
                        isset($required[$bodyItemName])
                    );
                }

                continue;
            }

            // body as scalar
            $this->mergeStrategy->addParameter(
                $parameter->getIn(),
                $parameter->getName(),
                $parameterSchema->toArray() + ['title' => $parameter->getIn()],
                true === $parameter->getRequired()
            );
        }

        $mergedSchema = new Schema();
        $mergedSchema->merge([
            'type' => 'object',
            'properties' => $this->mergeStrategy->getParameters(),
            'required' => $this->mergeStrategy->getRequired(),
        ]);

        $this->mergeStrategy->clean();

        return $mergedSchema;
    }
}
