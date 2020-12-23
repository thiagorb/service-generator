<?php

namespace Thiagorb\ServiceGenerator\Configuration;

use Thiagorb\ServiceGenerator\Definitions\Method;
use Thiagorb\ServiceGenerator\Definitions\Parameter;

class SnakeCaseConvention implements NamingConvention
{
    public function transformMethodName(Method $method): string
    {
        return $this->toSnakeCase($method->getName());
    }

    public function transformParameterName(Parameter $parameter): string
    {
        return $this->toSnakeCase($parameter->getName());
    }

    protected function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string) ?: '');
    }
}