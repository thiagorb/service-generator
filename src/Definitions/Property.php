<?php

namespace Thiagorb\ServiceGenerator\Definitions;

use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;

class Property
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var BaseType
     */
    protected $type;

    public function __construct(string $name, BaseType $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): BaseType
    {
        return $this->type;
    }
}
