<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class ReadResource extends AbstractResourceMessage implements OperationHandler
{
    protected function getHttpMethod(): string
    {
        return 'get';
    }
}
