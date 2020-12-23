<?php

namespace Thiagorb\ServiceGenerator\Configuration;

use Thiagorb\ServiceGenerator\Targets\GeneratorInterface;

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
     * @var GeneratorInterface
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
     * @var NamingConvention
     */
    protected $namingConvention;

    public function __construct(
        string $entryPointContract,
        GeneratorInterface $target,
        string $targetDirectory,
        string $targetNamespace,
        ?NamingConvention $namingConvention = null
    ) {
        $this->entryPointContract = $entryPointContract;
        $contractsNamespace = explode('\\', $entryPointContract);
        array_pop($contractsNamespace);
        $this->contractsNamespace = implode('\\', $contractsNamespace);
        $this->target = $target;
        $this->targetDirectory = $targetDirectory;
        $this->targetNamespace = $targetNamespace;
        $this->namingConvention = $namingConvention ?: new SnakeCaseConvention;
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

    public function getTarget(): GeneratorInterface
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

    public function getNamingConvention(): NamingConvention
    {
        return $this->namingConvention;
    }
}