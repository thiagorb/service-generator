<?php

namespace Thiagorb\ServiceGenerator;

use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\Definitions\File;

class Generator
{
    /**
     * @param ServiceConfiguration[] $serviceConfigurations
     *
     * @throws \ReflectionException
     */
    public function generate(iterable $serviceConfigurations)
    {
        foreach ($serviceConfigurations as $serviceConfiguration) {
            $typeResolver = new TypeResolver();
            /** @var File $file */
            foreach ($serviceConfiguration->getTarget()->generate($serviceConfiguration, $typeResolver) as $file) {
                if (!file_exists(dirname($file->getPath()))) {
                    mkdir(dirname($file->getPath()), 0755, true);
                }
                file_put_contents($file->getPath(), $file->getContent());
            }
        }
    }
}
