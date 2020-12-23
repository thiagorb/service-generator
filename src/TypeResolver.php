<?php

namespace Thiagorb\ServiceGenerator;

use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;
use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;
use Thiagorb\ServiceGenerator\Definitions\Types\ClassType;
use Thiagorb\ServiceGenerator\Definitions\Types\PrimitiveType;
use Thiagorb\ServiceGenerator\Definitions\Types\VoidType;

class TypeResolver
{
    /**
     * @var BaseType[]
     */
    protected $resolvedTypes = [];

    /**
     * @psalm-var array<string, true>
     */
    protected static $primitives = [
        'bool' => true,
        'float' => true,
        'int' => true,
        'string' => true,
    ];

    public function resolve(string $type): BaseType
    {
        if (isset($this->resolvedTypes[$type])) {
            return $this->resolvedTypes[$type];
        }

        if (class_exists($type)) {
            return $this->resolveTypeWithMethods($type, ClassType::class);
        }

        /**
         * @psalm-suppress ArgumentTypeCoercion
         */
        if (interface_exists($type)) {
            return $this->resolveTypeWithMethods($type, InterfaceType::class);
        }

        if (static::$primitives[$type] ?? false) {
            return new PrimitiveType($type);
        }

        if ($type === 'void') {
            return new VoidType();
        }

        throw new \Error('Unable to resolve type ' . $type);
    }

    public function resolveDocBlockTypeName(string $type): ?BaseType
    {
        $docBlockResolver = new DocBlockTypeResolver($this, new \ReflectionClass(\stdClass::class));
        return $docBlockResolver->resolveDocBlockTypeName($type);
    }

    /**
     * @template T as object
     * @template B of BaseType
     *
     * @psalm-param class-string<T> $typeName
     * @psalm-param class-string<B> $concreteType
     */
    protected function resolveTypeWithMethods(string $typeName, string $concreteType): BaseType
    {
        $methodsContainer = new \stdClass;
        $type = new $concreteType($typeName, $methodsContainer);
        $this->resolvedTypes[$typeName] = $type;
        $methodsContainer->methods = $this->resolveMethods($typeName);
        return $type;
    }

    /**
     * @template T as object
     *
     * @psalm-param class-string<T> $type
     */
    protected function resolveMethods(string $type): array
    {
        return (new DocBlockTypeResolver($this, new \ReflectionClass($type)))->buildDefinitions();
    }

    /**
     * @return BaseType[]
     */
    public function getResolvedTypes(): array
    {
        return $this->resolvedTypes;
    }
}
