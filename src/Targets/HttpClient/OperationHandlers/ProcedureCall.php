<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class ProcedureCall extends AbstractResourceMessage implements OperationHandler
{
    public function buildMetaData(): ?array
    {
        return [
            'relative_path' => $this->methodContext->getMethod()->getName(),
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
}
