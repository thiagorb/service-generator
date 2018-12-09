<?php

namespace Thiagorb\ServiceGenerator\Definitions;

class File
{
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $content;

    public function __construct(
        string $path,
        string $content
    ) {
        $this->path = $path;
        $this->content = $content;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}