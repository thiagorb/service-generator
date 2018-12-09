<?php

namespace Tests;

use Thiagorb\ServiceGenerator\TypeResolver;
use Thiagorb\ServiceGenerator\MethodTypeResolver;
use Thiagorb\ServiceGenerator\Definitions\Types\PrimitiveType;
use Thiagorb\ServiceGenerator\Definitions\Types\NullableType;
use Thiagorb\ServiceGenerator\Definitions\Types\VoidType;

class MethodTypeResolverTest extends TestCase
{
    public function testReturnPrimitive()
    {
        $obj = new class { public function returnInt(): int { return 0; } };

        $typeResolver = new MethodTypeResolver(new TypeResolver(), new \ReflectionClass($obj));
        list('returnInt' => $actualReturnInt) = $typeResolver->buildDefinitions();
        $this->assertInstanceOf(PrimitiveType::class, $actualReturnInt->getReturnType());
        $this->assertEquals('int', $actualReturnInt->getReturnType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => false], $actualReturnInt->getTypeHintReturnType());
    }

    public function testReturnNullable()
    {
        $obj = new class
        {
            public function returnNullable(): ?int
            {
                return 0;
            }
        };

        $typeResolver = new MethodTypeResolver(new TypeResolver(), new \ReflectionClass($obj));
        list('returnNullable' => $actualReturnNullable) = $typeResolver->buildDefinitions();
        $this->assertInstanceOf(NullableType::class, $actualReturnNullable->getReturnType());
        $this->assertEquals('int', $actualReturnNullable->getReturnType()->getInnerType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => true], $actualReturnNullable->getTypeHintReturnType());
    }

    public function testPrimitiveParameters()
    {
        $obj = new class { public function parametersInt(int $a, int $b = 0, int $c = null): void {} };

        $typeResolver = new MethodTypeResolver(new TypeResolver(), new \ReflectionClass($obj));
        list('parametersInt' => $actualParametersInt) = $typeResolver->buildDefinitions();
        $this->assertInstanceOf(VoidType::class, $actualParametersInt->getReturnType());
        $this->assertEquals(['type' => 'void', 'nullable' => false], $actualParametersInt->getTypeHintReturnType());
        $this->assertCount(3, $actualParametersInt->getParameters());
        list('a' => $actualA, 'b' => $actualB, 'c' => $actualC) = $actualParametersInt->getParameters();
        $this->assertEquals('a', $actualA->getName());
        $this->assertInstanceOf(PrimitiveType::class, $actualA->getType());
        $this->assertEquals('int', $actualA->getType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => false], $actualA->getTypeHintType());
        $this->assertFalse($actualA->hasDefaultValue());

        $this->assertEquals('b', $actualB->getName());
        $this->assertInstanceOf(PrimitiveType::class, $actualB->getType());
        $this->assertEquals('int', $actualB->getType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => false], $actualB->getTypeHintType());
        $this->assertTrue($actualB->hasDefaultValue());
        $this->assertEquals(0, $actualB->getDefaultValue());

        $this->assertEquals('c', $actualC->getName());
        $this->assertInstanceOf(NullableType::class, $actualC->getType());
        $this->assertInstanceOf(PrimitiveType::class, $actualC->getType()->getInnerType());
        $this->assertEquals('int', $actualC->getType()->getInnerType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => true], $actualC->getTypeHintType());
        $this->assertTrue($actualC->hasDefaultValue());
        $this->assertEquals(null, $actualB->getDefaultValue());
    }

    public function testNullableParameters()
    {
        $obj = new class { public function parametersNullable(?int $a, ?int $b = 0, ?int $c = null): void {} };

        $typeResolver = new MethodTypeResolver(new TypeResolver(), new \ReflectionClass($obj));

        list('parametersNullable' => $actualParametersNullable) = $typeResolver->buildDefinitions();
        $this->assertInstanceOf(VoidType::class, $actualParametersNullable->getReturnType());
        $this->assertEquals(['type' => 'void', 'nullable' => false], $actualParametersNullable->getTypeHintReturnType());
        $this->assertCount(3, $actualParametersNullable->getParameters());
        list('a' => $actualA, 'b' => $actualB, 'c' => $actualC) = $actualParametersNullable->getParameters();
        $this->assertEquals('a', $actualA->getName());
        $this->assertInstanceOf(NullableType::class, $actualA->getType());
        $this->assertInstanceOf(PrimitiveType::class, $actualA->getType()->getInnerType());
        $this->assertEquals('int', $actualA->getType()->getInnerType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => true], $actualA->getTypeHintType());
        $this->assertFalse($actualA->hasDefaultValue());

        $this->assertEquals('b', $actualB->getName());
        $this->assertInstanceOf(NullableType::class, $actualB->getType());
        $this->assertInstanceOf(PrimitiveType::class, $actualB->getType()->getInnerType());
        $this->assertEquals('int', $actualB->getType()->getInnerType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => true], $actualB->getTypeHintType());
        $this->assertTrue($actualB->hasDefaultValue());
        $this->assertEquals(0, $actualB->getDefaultValue());

        $this->assertEquals('c', $actualC->getName());
        $this->assertInstanceOf(NullableType::class, $actualC->getType());
        $this->assertInstanceOf(PrimitiveType::class, $actualC->getType()->getInnerType());
        $this->assertEquals('int', $actualC->getType()->getInnerType()->getName());
        $this->assertEquals(['type' => 'int', 'nullable' => true], $actualC->getTypeHintType());
        $this->assertTrue($actualC->hasDefaultValue());
        $this->assertEquals(null, $actualB->getDefaultValue());
    }
}