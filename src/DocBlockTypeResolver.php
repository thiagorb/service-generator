<?php

namespace Thiagorb\ServiceGenerator;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use phpDocumentor\Reflection\Types\Float_;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Nullable;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void_;
use Thiagorb\ServiceGenerator\Definitions\Types\ArrayType;
use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;
use Thiagorb\ServiceGenerator\Definitions\Types\NullableType;
use Thiagorb\ServiceGenerator\Definitions\Types\PrimitiveType;
use Thiagorb\ServiceGenerator\Definitions\Types\VoidType;
use Thiagorb\ServiceGenerator\Definitions\Method;
use Thiagorb\ServiceGenerator\Definitions\Parameter;
use phpDocumentor\Reflection\Types\Mixed_;
use phpDocumentor\Reflection\Types\Null_;
use phpDocumentor\Reflection\Types\Self_;

class DocBlockTypeResolver
{
    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var DocBlockFactory
     */
    protected $docBlockFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var DocBlock[]
     */
    protected $methodsDocBlocks = [];

    public function __construct(TypeResolver $typeResolver, \ReflectionClass $reflectionClass)
    {
        $this->typeResolver = $typeResolver;
        $this->reflectionClass = $reflectionClass;
        $this->docBlockFactory = DocBlockFactory::createInstance();
        $this->context = (new ContextFactory)->createFromReflector($reflectionClass);
    }

    /**
     * @return Method[]
     */
    public function buildDefinitions(): array
    {
        $methods = [];
        foreach ($this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[$method->getName()] = $this->buildMethodDefinition($method);
        }
        return $methods;
    }

    public function resolveDocBlockTypeName(string $typeName): ?BaseType
    {
        $tag = $this->docBlockFactory->create("@var {$typeName}")->getTags()[0];
        return $this->resolveDocBlockType($tag->getType());
    }

    protected function buildMethodDefinition(\ReflectionMethod $method): Method
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = new Parameter(
                $parameter->getName(),
                $this->resolveParameterType($method, $parameter),
                $this->getTypeHint($parameter->getType()),
                $parameter->isDefaultValueAvailable()
                    ? (object)['value' => $parameter->getDefaultValue()] : null
            );
        }

        return new Method(
            $method->getName(),
            $parameters,
            $this->resolveReturnType($method),
            $method->getDocComment() ?: null,
            $this->getTypeHint($method->getReturnType())
        );
    }

    protected function resolveReturnType(\ReflectionMethod $method): BaseType
    {
        if ($method->getName() === '__construct') {
            return new VoidType();
        }

        if (($returnTag = $this->getMethodTags($method, 'return')[0] ?? null) instanceof Return_) {
            if ($type = $this->resolveDocBlockType($returnTag->getType())) {
                return $type;
            }
        }

        return $this->resolveReflectionType($method->getReturnType());
    }

    protected function resolveReflectionType(?\ReflectionType $reflectionType): BaseType
    {
        if (!$reflectionType) {
            throw new \Error('Unable to resolve type');
        }

        if ($reflectionType->getName() === 'self') {
            return $this->typeResolver->resolve($this->reflectionClass->getName());
        }

        $type = $this->typeResolver->resolve($reflectionType->getName());
        return $reflectionType->allowsNull() ? new NullableType($type) : $type;
    }

    /**
     * @param \ReflectionMethod $method
     * @param string            $tagName
     *
     * @return DocBlock\Tag[]
     */
    protected function getMethodTags(\ReflectionMethod $method, string $tagName): array
    {
        if (!$method->getDocComment()) {
            return [];
        }

        if (!isset($this->methodsDocBlocks[$method->getName()])) {
            $this->methodsDocBlocks[$method->getName()] = $this->docBlockFactory->create($method->getDocComment() ?: '', $this->context);
        }

        return $this->methodsDocBlocks[$method->getName()]->getTagsByName($tagName);
    }

    /**
     * @param Type $type
     *
     * @return BaseType|null
     */
    protected function resolveDocBlockType(Type $type): ?BaseType
    {
        $primitiveHandler = function (Type $type): BaseType {
            return new PrimitiveType((string)$type->__toString());
        };

        /** @var callable[] $resolvers */
        $resolvers = [
            Array_::class => function (Array_ $type): BaseType {
                if (($valueType = $type->getValueType()) instanceof Mixed_) {
                    throw new \Error('Type of array items must be known');
                }

                if (!($resolvedValueType = $this->resolveDocBlockType($valueType))) {
                    throw new \Error('Unable to resolve type of array items');
                }

                return new ArrayType($resolvedValueType);
            },
            Boolean::class => $primitiveHandler,
            // Callable_::class
            Compound::class => function (Compound $type): BaseType {
                $nullType = null;
                $innerType = null;
                $nonNulls = 0;

                foreach ($type as $subtype) {
                    if ($subtype instanceof Null_) {
                        $nullType = $type;
                    } else if (is_null($innerType)) {
                        $innerType = $this->resolveDocBlockType($subtype);
                        $nonNulls++;
                    }
                }

                if (is_null($nullType) || $nonNulls > 1) {
                    throw new \Error('The only compound type supported is `T|null`');
                }

                return new NullableType($innerType);
            },
            Float_::class => $primitiveHandler,
            Integer::class => $primitiveHandler,
            // Iterable_::class
            // Mixed_::class
            // Null_::class
            Nullable::class => function (Nullable $type): BaseType {
                if (($actualType = $type->getActualType()) instanceof Mixed_) {
                    throw new \Error('Type of nullable must be known');
                }

                if (!($resolvedActualType = $this->resolveDocBlockType($actualType))) {
                    throw new \Error('Unable to resolve type of nullable');
                }

                return new NullableType($resolvedActualType);
            },
            Object_::class => function (Object_ $type): BaseType {
                return $this->typeResolver->resolve(
                    trim((string)$type->getFqsen() ?: '', '\\')
                );
            },
            // Parent_::class
            // Resource_::class
            // Scalar::class
            Self_::class => function (Self_ $type): BaseType {
                return $this->typeResolver->resolve($this->reflectionClass->getName());
            },
            // Static_::class
            String_::class => $primitiveHandler,
            // This::class
            Void_::class => function (): BaseType {
                return new VoidType();
            },
        ];

        if ($resolver = $resolvers[get_class($type)] ?? null) {
            return $resolver($type);
        }

        return null;
    }

    protected function resolveParameterType(\ReflectionMethod $method, \ReflectionParameter $parameter): BaseType
    {
        foreach ($this->getMethodTags($method, 'param') as $tag) {
            if (!($tag instanceof DocBlock\Tags\Param) || $tag->getVariableName() !== $parameter->getName()) {
                continue;
            }

            if (($tagType = $tag->getType()) && ($type = $this->resolveDocBlockType($tagType))) {
                return $type;
            }
        }

        return $this->resolveReflectionType($parameter->getType());
    }

    protected function getTypeHint(?\ReflectionType $type): array
    {
        if (!$type) {
            return ['type' => null, 'nullable' => false];
        }

        return ['type' => $type->getName(), 'nullable' => $type->allowsNull()];
    }
}
