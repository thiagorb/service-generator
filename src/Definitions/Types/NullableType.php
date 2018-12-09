<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

class NullableType implements BaseType
{
    /**
     * @var BaseType
     */
    protected $inner;

    public function __construct(BaseType $inner)
    {
        $this->inner = $inner;
    }

    public function getInnerType(): BaseType
    {
        return $this->inner;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitNullable($this);
    }
}
