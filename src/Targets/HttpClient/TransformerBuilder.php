<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Nette\PhpGenerator\ClassType as GeneratorClassType;
use Nette\PhpGenerator\PhpFile;
use Thiagorb\ServiceGenerator\Configuration\Service;
use Thiagorb\ServiceGenerator\Definitions\File as FileDefinition;
use Thiagorb\ServiceGeneratorRuntime\Transformers\ImmutableObjectTransformer;
use Thiagorb\ServiceGenerator\Definitions\Types\ClassType;
use Thiagorb\ServiceGenerator\Definitions\Types\NullableType;

class TransformerBuilder
{
    /**
     * @var TransformerResolver
     */
    protected $transformerResolver;

    /**
     * @var Service
     */
    protected $configuration;

    /**
     * @var ClassType
     */
    protected $objectClass;

    public function __construct(TransformerResolver $transformerResolver, Service $configuration, ClassType $objectClass)
    {
        $this->transformerResolver = $transformerResolver;
        $this->configuration = $configuration;
        $this->objectClass = $objectClass;
    }

    public function build(): FileDefinition
    {
        return new FileDefinition($this->getPath(), (string)$this->buildFile());
    }

    protected function getTransformerNamespace(): string
    {
        return implode(
            '\\',
            array_merge(
                [$this->configuration->getTargetNamespace()],
                $this->getTransformerRelativeNamespaceParts()
            )
        );
    }

    protected function getTransformerClass(): string
    {
        return $this->objectClass->getShortName() . 'Transformer';
    }

    /**
     * @return string[]
     */
    protected function getTransformerRelativeNamespaceParts(): array
    {
        $parts = array_filter(explode('\\', $this->objectClass->getFullName()));
        array_pop($parts);
        array_unshift($parts, 'Transformers');
        return $parts;
    }

    public function getTransformerFullClass(): string
    {
        return $this->getTransformerNamespace() . '\\' . $this->getTransformerClass();
    }

    protected function getPath(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_merge(
                [$this->configuration->getTargetDirectory()],
                $this->getTransformerRelativeNamespaceParts(),
                [$this->getTransformerClass() . '.php']
            )
        );
    }

    protected function buildFile(): PhpFile
    {
        $file = new PhpFile;
        $namespace = $file->addNamespace($this->getTransformerNamespace());
        $class = $namespace
            ->addClass($this->getTransformerClass())
            ->setType(GeneratorClassType::TYPE_CLASS)
            ->setExtends(ImmutableObjectTransformer::class);

        $class->addProperty('objectClass', $this->objectClass->getFullName())->setVisibility(GeneratorClassType::VISIBILITY_PROTECTED);
        $class->addProperty('propertiesParameters', $this->buildPropertiesParameters())->setVisibility(GeneratorClassType::VISIBILITY_PROTECTED);

        return $file;
    }

    protected function buildPropertiesParameters(): array
    {
        if (!($constructor = $this->objectClass->getMethods()['__construct'] ?? null)) {
            throw new \Error('Immutable objects must have a public constructor');
        }

        $properties = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $this->transformerResolver->transform($parameter->getType());
            if ($parameter->hasDefaultValue()) {
                $type['defaultValue'] = $parameter->getDefaultValue();
            }
            $type['nullable'] = $parameter->getType() instanceof NullableType;
            $properties[$parameter->getName()] = $type;
        }

        return $properties;
    }
}