<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

class ArrayType implements BaseType
{
    /**
     * @var BaseType
     */
    protected $itemType;

    public function __construct(BaseType $itemType)
    {
        $this->itemType = $itemType;
    }

    public function getItemType(): BaseType
    {
        return $this->itemType;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitArray($this);
    }
}
