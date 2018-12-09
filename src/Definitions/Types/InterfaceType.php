<?php

namespace Thiagorb\ServiceGenerator\Definitions\Types;

use Thiagorb\ServiceGenerator\Definitions\Method;

class InterfaceType implements BaseType
{
    /**
     * @var string
     */
    protected $fullName;

    /**
     * @var \stdClass
     */
    protected $methodsContainer;

    public function __construct(string $fullName, \stdClass $methodsContainer)
    {
        $this->fullName = $fullName;
        $this->methodsContainer = $methodsContainer;
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methodsContainer->methods;
    }

    public function accept(Visitor $visitor)
    {
        $visitor->visitInterface($this);
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getShortName(): string
    {
        $name = preg_replace('#^.*\\\\([^\\\\]+)$#', '\1', $this->fullName);

        if (!$name) {
            throw new \Error('Invalid class name');
        }

        return $name;
    }
}
