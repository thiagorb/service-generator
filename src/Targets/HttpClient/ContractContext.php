<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Thiagorb\ServiceGenerator\Definitions\Contract;
use Thiagorb\ServiceGenerator\Configuration\Service;
use Thiagorb\ServiceGenerator\Definitions\Method;

class ContractContext
{
    /**
     * @var Service
     */
    protected $service;

    /**
     * @var Contract
     */
    protected $contract;

    public function __construct(Service $service, Contract $contract)
    {
        $this->service = $service;
        $this->contract = $contract;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function getContract(): Contract
    {
        return $this->contract;
    }

    public function withMethod(Method $method): MethodContext
    {
        return new MethodContext($this, $method);
    }
}