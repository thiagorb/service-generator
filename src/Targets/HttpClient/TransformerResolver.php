<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Thiagorb\ServiceGenerator\Definitions\Types\ArrayType;
use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;
use Thiagorb\ServiceGenerator\Definitions\Types\ClassType;
use Thiagorb\ServiceGenerator\Definitions\Types\NullableType;
use Thiagorb\ServiceGenerator\Definitions\Types\PrimitiveType;
use Thiagorb\ServiceGeneratorRuntime\Transformers\ArrayTransformer;
use Thiagorb\ServiceGeneratorRuntime\Transformers\PrimitiveTransformer;
use Thiagorb\ServiceGeneratorRuntime\Transformers\NullableTransformer;
use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\TypeResolver;
use Thiagorb\ServiceGenerator\Definitions\File;
use Thiagorb\ServiceGenerator\Definitions\Types\VoidType;

class TransformerResolver
{
    /**
     * @var TransformerBuilder[]
     */
    protected $builders = [];

    /**
     * @var TransformerBuilder[]
     */
    protected $generationQueue = [];

    /**
     * @var ServiceConfiguration
     */
    protected $serviceConfiguration;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    public function __construct(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver)
    {
        $this->serviceConfiguration = $serviceConfiguration;
        $this->typeResolver = $typeResolver;
    }

    public function transform(BaseType $type): array
    {
        if ($type instanceof PrimitiveType) {
            return ['transformer' => PrimitiveTransformer::class];
        }

        if ($type instanceof ArrayType) {
            return [
                'transformer' => ArrayTransformer::class,
                'arguments' => [$this->transform($type->getItemType())],
            ];
        }

        if ($type instanceof NullableType) {
            return [
                'transformer' => NullableTransformer::class,
                'arguments' => [$this->transform($type->getInnerType())],
            ];
        }

        if ($type instanceof ClassType) {
            if (!($builder = $this->builders[$type->getFullName()] ?? null)) {
                $builder = new TransformerBuilder($this, $this->serviceConfiguration, $type);
                $this->builders[$type->getFullName()] = $builder;
                $this->generationQueue[] = $builder;
            }
            return ['transformer' => $builder->getTransformerFullClass()];
        }

        if ($type instanceof VoidType) {
            return [
                'transformer' => NullableTransformer::class,
                'arguments' => [['transformer' => PrimitiveTransformer::class]],
            ];
        }

        throw new \Error('Unable to resolve type transformer');
    }

    /**
     * @psalm-return \Generator<File>
     */
    public function generate(): \Generator
    {
        while ($this->generationQueue) {
            /** @var TransformerBuilder $builder */
            $builder = array_shift($this->generationQueue);
            yield $builder->build();
        }
    }
}