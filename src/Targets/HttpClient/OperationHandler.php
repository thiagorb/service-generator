<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

interface OperationHandler
{
    public function buildBody(): string;

    public function buildMetaData(): ?array;
}