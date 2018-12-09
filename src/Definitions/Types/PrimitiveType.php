<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

class PrimitiveType implements BaseType
{
    /**
     * @var string
     */
    protected $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitPrimitive($this);
    }
}
