<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

interface BaseType
{
    public function accept(Visitor $visitor);
}
