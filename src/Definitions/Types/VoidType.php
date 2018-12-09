<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

class VoidType implements BaseType
{
    public function accept(Visitor $visitor)
    {
        $visitor->visitVoid($this);
    }
}
