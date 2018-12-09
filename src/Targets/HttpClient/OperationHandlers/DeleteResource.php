<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class DeleteResource extends AbstractResourceMessage implements OperationHandler
{
    protected function getHttpMethod(): string
    {
        return 'delete';
    }
}
