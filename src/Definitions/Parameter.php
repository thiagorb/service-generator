<?php

namespace Thiagorb\ServiceGenerator\Definitions;

use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;

class Parameter
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var BaseType
     */
    protected $type;
    /**
     * @var array
     */
    protected $typeHintType;
    /**
     * @var \stdClass|null
     */
    protected $defaultValue;

    public function __construct(
        string $name,
        BaseType $type,
        array $typeHintType,
        \stdClass $defaultValue = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->typeHintType = $typeHintType;
        $this->defaultValue = $defaultValue;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): BaseType
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getTypeHintType(): array
    {
        return $this->typeHintType;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        if (!$this->defaultValue) {
            throw new \Error('Attempt to access undefined default value of parameter');
        }

        return $this->defaultValue->value;
    }

    /**
     * @return mixed
     */
    public function hasDefaultValue()
    {
        return $this->defaultValue !== null;
    }
}
