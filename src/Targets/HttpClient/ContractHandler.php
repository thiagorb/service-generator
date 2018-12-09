<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

interface ContractHandler
{
    public function generate(): \Traversable;

    public function getMetaData(): array;

    /**
     * @return string[]
     */
    public function getSubcontracts(): array;
}