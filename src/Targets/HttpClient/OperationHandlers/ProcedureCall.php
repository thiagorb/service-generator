<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class ProcedureCall extends AbstractResourceMessage implements OperationHandler
{
    public function buildMetaData(): ?array
    {
        return [
            'relative_path' => $this->decamelize($this->methodContext->getMethod()->getName()),
            'http_method' => 'post',
            'parameters' => $this->buildMethodParametersMetaData(),
            /** @todo: implement exceptions inference */
            'exceptions' => [],
            'return_type' => $this->factory->getTransformerResolver()->transform($this->methodContext->getMethod()->getReturnType()),
        ];
    }

    protected function getHttpMethod(): string
    {
        return 'post';
    }

    protected function decamelize(string $string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string) ?: '');
    }
}
