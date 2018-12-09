<?php

namespace Thiagorb\ServiceGenerator\Targets;

use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\TypeResolver;

interface GeneratorInterface
{
    public function generate(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver): \Traversable;
}