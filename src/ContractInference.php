<?php

namespace Thiagorb\ServiceGenerator;

use Thiagorb\ServiceGenerator\Definitions\Contract;
use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;

class ContractInference
{
    /**
     * @var string
     */
    protected $baseNamespace;

    /**
     * @var string
     */
    protected $contractInterface;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * ContractInference constructor.
     *
     * @param string       $baseNamespace
     * @param string       $contractInterface
     * @param TypeResolver $typeResolver
     *
     * @throws \ReflectionException
     */
    public function __construct(string $baseNamespace, string $contractInterface, TypeResolver $typeResolver)
    {
        $this->baseNamespace = $baseNamespace;
        $this->contractInterface = $contractInterface;
        $this->typeResolver = $typeResolver;
    }

    public function buildDefinition(): Contract
    {
        $contractType = $this->typeResolver->resolve($this->contractInterface);

        if (!$contractType instanceof InterfaceType) {
            throw new \Error('A contract must be an interface');
        }

        return new Contract($contractType);
    }
}
