<?php

namespace Thiagorb\ServiceGenerator\Configuration;

use Thiagorb\ServiceGenerator\Definitions\Method;
use Thiagorb\ServiceGenerator\Definitions\Parameter;

interface NamingConvention
{
    public function transformMethodName(Method $method): string;

    public function transformParameterName(Parameter $parameter): string;
}