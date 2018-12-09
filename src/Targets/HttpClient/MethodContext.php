<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Thiagorb\ServiceGenerator\Definitions\Contract;
use Thiagorb\ServiceGenerator\Configuration\Service;
use Thiagorb\ServiceGenerator\Definitions\Method;

class MethodContext
{
    /**
     * @var ContractContext
     */
    protected $contractContext;

    /**
     * @var Method
     */
    protected $method;

    public function __construct(ContractContext $contractContext, Method $method)
    {
        $this->contractContext = $contractContext;
        $this->method = $method;
    }

    public function getService(): Service
    {
        return $this->contractContext->getService();
    }

    public function getContract(): Contract
    {
        return $this->contractContext->getContract();
    }

    public function getMethod(): Method
    {
        return $this->method;
    }
}