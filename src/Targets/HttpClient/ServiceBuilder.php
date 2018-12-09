<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Thiagorb\ServiceGenerator\Configuration\Service;
use Thiagorb\ServiceGenerator\Definitions\File as FileDefinition;
use Thiagorb\ServiceGeneratorRuntime\ServiceContext;

class ServiceBuilder
{
    /**
     * @var Service
     */
    protected $configuration;

    /**
     * @var array
     */
    protected $definitions;

    public function __construct(Service $configuration, array $definitions)
    {
        $this->configuration = $configuration;
        $this->definitions = $definitions;
    }

    public function build(): FileDefinition
    {
        return new FileDefinition($this->getPath(), (string)$this->buildFile());
    }

    protected function getPath(): string
    {
        return $this->configuration->getTargetDirectory() . DIRECTORY_SEPARATOR . $this->getGeneratedClassName() . '.php';
    }

    protected function getGeneratedClassName(): string
    {
        return 'Service';
    }

    protected function buildFile(): PhpFile
    {
        $file = new PhpFile;
        $namespace = $file->addNamespace($this->configuration->getTargetNamespace());
        $class = $namespace
            ->addClass($this->getGeneratedClassName())
            ->setType(ClassType::TYPE_CLASS)
            ->setExtends(ServiceContext::class);

        $class->addProperty('meta', $this->definitions);

        return $file;
    }
}