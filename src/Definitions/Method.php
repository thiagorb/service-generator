<?php

namespace Thiagorb\ServiceGenerator\Definitions;

use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;

class Method
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var Parameter[]
     */
    protected $parameters;
    /**
     * @var BaseType
     */
    protected $returnType;
    /**
     * @var ?string
     */
    protected $comment;
    /**
     * @var array
     */
    protected $typeHintReturnType;

    /**
     * @param string      $name
     * @param Parameter[] $parameters
     * @param BaseType    $returnType
     * @param ?string     $comment
     * @param array       $typeHintReturnType
     */
    public function __construct(
        string $name,
        array $parameters,
        BaseType $returnType,
        ?string $comment,
        array $typeHintReturnType
    ) {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->comment = $comment;
        $this->typeHintReturnType = $typeHintReturnType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Parameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getReturnType(): BaseType
    {
        return $this->returnType;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTypeHintReturnType(): array
    {
        return $this->typeHintReturnType;
    }
}
