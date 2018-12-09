<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

abstract class Visitor
{
    public function visitArray(ArrayType $type)
    {
    }

    public function visitInterface(InterfaceType $type)
    {
    }

    public function visitClass(ClassType $type)
    {
    }

    public function visitNullable(NullableType $type)
    {
    }

    public function visitPrimitive(PrimitiveType $type)
    {
    }

    public function visitVoid(VoidType $type)
    {
    }
}