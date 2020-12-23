<?php

namespace Thiagorb\ServiceGenerator\Targets\Models;

use Nette\PhpGenerator\ClassType;
use Thiagorb\ServiceGenerator\Definitions\File;
use Nette\PhpGenerator\PhpFile;
use Thiagorb\ServiceGenerator\Targets\GeneratorInterface;
use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\TypeResolver;

class Generator implements GeneratorInterface
{
    /**
     * @var string
     */
    protected $modelsPath;

    public function __construct(string $modelsPath)
    {
        $this->modelsPath = $modelsPath;
    }

    public function generate(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver): \Traversable
    {
        $baseNamespace = explode('\\', $serviceConfiguration->getTargetNamespace());
        $propertyTypeResolver = new PropertyTypeResolver();

        foreach ($this->getModelsGenerator() as $model) {
            $file = new PhpFile;
            $namespace = $file->addNamespace(implode('\\', array_merge($baseNamespace, $model['relative_namespace'] ?? [])));
            $class = $namespace->addClass($model['name']);
            $constructor = $class->addMethod('__construct');
            $constructorBody = [];
            $constructorDocBlock = [];
            foreach ($model['properties'] as $propertyName => $propertyData) {
                $property = $class
                    ->addProperty($propertyName)
                    ->setVisibility(ClassType::VISIBILITY_PROTECTED)
                    ->setComment(sprintf('@var %s', $propertyData['type']));

                $getter = $class->addMethod('get' . ucfirst($propertyName))
                    ->setVisibility(ClassType::VISIBILITY_PUBLIC)
                    ->setReturnType($propertyTypeResolver->resolveTypeHint($propertyData))
                    ->setBody(sprintf('return $this->%s;', $propertyName));

                $with = $class->addMethod('with' . ucfirst($propertyName))
                    ->setVisibility(ClassType::VISIBILITY_PUBLIC)
                    ->setReturnType('self')
                    ->setComment(sprintf('@param %s $%s', $propertyData['type'], $propertyName))
                    ->setBody(
                        "\$clone = clone(\$this);\n\$clone->$propertyName = \$$propertyName;\nreturn \$clone;"
                    );
                $withParam = $with
                    ->addParameter($propertyName)
                    ->setType($propertyTypeResolver->resolveTypeHint($propertyData));

                if ($propertyTypeResolver->isNullable($propertyData)) {
                    $getter->setReturnNullable(true);
                    $withParam->setNullable(true);
                } else {
                    $constructorParam = $constructor
                        ->addParameter($propertyName)
                        ->setType($propertyTypeResolver->resolveTypeHint($propertyData));

                    if (array_key_exists('defaultValue', $propertyData)) {
                        $constructorParam->setDefaultValue($propertyData['defaultValue']);
                    }

                    $constructorDocBlock[] = sprintf('@param %s $%s', $propertyData['type'], $propertyName);
                    $constructorBody[] = sprintf('$this->%s = $%s;', $propertyName, $propertyName);
                }

                $getter->setComment('@return ' . $propertyData['type']);
            }

            foreach ($model['properties'] as $propertyName => $propertyData) {
                if ($propertyTypeResolver->isNullable($propertyData)) {
                    $constructorParam = $constructor
                        ->addParameter($propertyName)
                        ->setType($propertyTypeResolver->resolveTypeHint($propertyData))
                        ->setNullable(true);

                    if (array_key_exists('defaultValue', $propertyData)) {
                        $constructorParam->setDefaultValue($propertyData['defaultValue']);
                    }

                    $constructorDocBlock[] = sprintf('@param %s $%s', $propertyData['type'], $propertyName);
                    $constructorBody[] = sprintf('$this->%s = $%s;', $propertyName, $propertyName);
                }
            }

            $constructor->setComment(implode(PHP_EOL, $constructorDocBlock));
            $constructor->setBody(implode(PHP_EOL, $constructorBody));
            yield new File($this->getPath($serviceConfiguration, $model), $file->__toString());
        }
    }

    protected function getModelsGenerator(array $relativePath = [], \RecursiveDirectoryIterator $it = null)
    {
        if (is_null($it)) {
            $it = new \RecursiveDirectoryIterator(
                $this->modelsPath,
                \FilesystemIterator::SKIP_DOTS
            );
        }

        /** @var \SplFileInfo $file */
        foreach ($it as $file) {
            if ($file->isDir()) {
                yield from $this->getModelsGenerator(array_merge($relativePath, [$file->getFilename()]), $it->getChildren());
            } else {
                yield [
                    'relative_namespace' => $relativePath,
                    'name' => $file->getBasename('.php'),
                    'properties' => include($file->getPathname()),
                ];
            }
        }
    }

    protected function getPath(ServiceConfiguration $serviceConfiguration, array $model): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_merge(
                [$serviceConfiguration->getTargetDirectory()],
                $model['relative_namespace'] ?? [],
                [$model['name'] . '.php']
            )
        );
    }
}
