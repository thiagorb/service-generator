<?php

namespace Thiagorb\ServiceGenerator\Definitions;

use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;

class Contract
{
    /**
     * @var InterfaceType
     */
    protected $contractInterface;

    /**
     * @param InterfaceType $contractInterface
     */
    public function __construct(InterfaceType $contractInterface)
    {
        $this->contractInterface = $contractInterface;
    }

    /**
     * @return string
     */
    public function getShortClassName(): string
    {
        return $this->contractInterface->getShortName();
    }

    /**
     * @return string
     */
    public function getFullClassName(): string
    {
        return $this->contractInterface->getFullName();
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->contractInterface->getMethods();
    }
}
