<?php

namespace Thiagorb\ServiceGenerator;

use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\Definitions\File;
use Thiagorb\ServiceGenerator\Targets\GeneratorInterface;

class Generator
{
    /**
     * @param ServiceConfiguration[] $serviceConfigurations
     *
     * @throws \ReflectionException
     */
    public function generate(array $serviceConfigurations)
    {
        /** @var ServiceConfiguration $serviceConfiguration */
        foreach ($serviceConfigurations as $serviceConfiguration) {
            $typeResolver = new TypeResolver();
            $targetGenerator = $this->getTargetGenerator($serviceConfiguration->getTarget());
            /** @var File $file */
            foreach ($targetGenerator->generate($serviceConfiguration, $typeResolver) as $file) {
                if (!file_exists(dirname($file->getPath()))) {
                    mkdir(dirname($file->getPath()), 0755, true);
                }
                file_put_contents($file->getPath(), $file->getContent());
            }
        }
    }

    /**
     * @psalm-param class-string $target
     */
    protected function getTargetGenerator(string $target): GeneratorInterface
    {
        $generator = new $target();

        if (!$generator instanceof GeneratorInterface) {
            throw new \Error('Generator must implement the generator interface');
        }

        return $generator;
    }
}
