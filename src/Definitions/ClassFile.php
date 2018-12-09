<?php

namespace Thiagorb\ServiceGenerator\Definitions;

class ClassFile extends File
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $name;

    public function __construct(
        string $namespace,
        string $name,
        string $path,
        string $content
    ) {
        $this->namespace = $namespace;
        $this->name = $name;
        parent::__construct($path, $content);
    }

    public function getFullClassName(): string
    {
        return $this->getNamespace() . '\\' . $this->getName();
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getName(): string
    {
        return $this->name;
    }
}