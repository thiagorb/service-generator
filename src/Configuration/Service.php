<?php

namespace Thiagorb\ServiceGenerator\Configuration;

class Service
{
    /**
     * @var string
     */
    protected $entryPointContract;
    /**
     * @var string
     */
    protected $contractsNamespace;
    /**
     * @psalm-var class-string
     * @var string
     */
    protected $target;
    /**
     * @var string
     */
    protected $targetDirectory;
    /**
     * @var string
     */
    protected $targetNamespace;

    /**
     * @psalm-param class-string $target
     */
    public function __construct(
        string $entryPointContract,
        string $target,
        string $targetDirectory,
        string $targetNamespace
    ) {
        $this->entryPointContract = $entryPointContract;
        $contractsNamespace = explode('\\', $entryPointContract);
        array_pop($contractsNamespace);
        $this->contractsNamespace = implode('\\', $contractsNamespace);
        $this->target = $target;
        $this->targetDirectory = $targetDirectory;
        $this->targetNamespace = $targetNamespace;
    }

    /**
     * @return string
     */
    public function getEntryPointContract(): string
    {
        return $this->entryPointContract;
    }

    /**
     * @return string
     */
    public function getContractsNamespace(): string
    {
        return $this->contractsNamespace;
    }

    /**
     * @psalm-return class-string
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * @return string
     */
    public function getTargetNamespace(): string
    {
        return $this->targetNamespace;
    }
}