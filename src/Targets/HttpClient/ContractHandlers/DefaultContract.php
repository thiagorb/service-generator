<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\ContractHandlers;

use Thiagorb\ServiceGenerator\Definitions\ClassFile;
use Thiagorb\ServiceGenerator\Targets\HttpClient\ContractHandler;
use Thiagorb\ServiceGenerator\Targets\HttpClient\Factory;
use Thiagorb\ServiceGenerator\Targets\HttpClient\ContractContext;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\AbstractSubcontractMessage;
use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;

class DefaultContract implements ContractHandler
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var ContractContext
     */
    protected $contractContext;

    public function __construct(Factory $factory, ContractContext $contractContext)
    {
        $this->factory = $factory;
        $this->contractContext = $contractContext;
    }

    public function generate(): \Traversable
    {
        $namespace = $this->getGeneratedNamespace();
        $className = $this->getGeneratedClassName();
        $content = $this->factory->makeClientBuilder($this->contractContext)->build($namespace, $className);
        yield new ClassFile($namespace, $className, $this->getPath(), $content);
    }

    public function getMetaData(): array
    {
        return [
            'implementation' => $this->getGeneratedFullClassName(),
        ];
    }

    /**
     * @return string[]
     */
    public function getSubcontracts(): array
    {
        $subcontracts = [];

        foreach ($this->contractContext->getContract()->getMethods() as $method) {
            $returnType = $method->getReturnType();
            $operationHandler = $this->factory->makeOperationHandler($this->contractContext->withMethod($method));
            if ($operationHandler instanceof AbstractSubcontractMessage && $returnType instanceOf InterfaceType) {
                $subcontracts[] = $returnType->getFullName();
            }
        }

        return $subcontracts;
    }

    protected function getGeneratedClassName(): string
    {
        return $this->contractContext->getContract()->getShortClassName();
    }

    protected function getGeneratedNamespace(): string
    {
        return implode(
            '\\',
            array_filter(
                [
                    $this->contractContext->getService()->getTargetNamespace(),
                    'Implementations',
                    $this->getRelativeNamespace(),
                ]
            )
        );
    }

    public function getGeneratedFullClassName(): string
    {
        return $this->getGeneratedNamespace() . '\\' . $this->getGeneratedClassName();
    }

    /**
     * Relative path of the namespace.
     * If the service namespace is \Example\Contracts, and the contract is \Example\Contracts\Post,
     * then the relative namespace is empty. If the contract is \Example\Contract\Post\Manage, then
     * the relative namespace is 'Post'
     */
    protected function getRelativeNamespace(): string
    {
        if (strpos($this->contractContext->getContract()->getFullClassName(), $this->contractContext->getService()->getContractsNamespace() . '\\') !== 0) {
            throw new \Error('The contract does not belong to the service contracts namespace');
        }

        $relativePath = substr($this->contractContext->getContract()->getFullClassName(), strlen($this->contractContext->getService()->getContractsNamespace()) + 1);
        $parts = explode('\\', $relativePath);
        array_pop($parts);
        return implode('\\', $parts);
    }

    protected function getPath(): string
    {
        return $this->getTargetDirectory() . DIRECTORY_SEPARATOR . $this->getGeneratedClassName() . '.php';
    }

    protected function getTargetDirectory(): string
    {
        return implode(
            DIRECTORY_SEPARATOR,
            array_merge(
                [$this->contractContext->getService()->getTargetDirectory(), 'Implementations'],
                array_filter(explode('\\', $this->getRelativeNamespace()))
            )
        );
    }
}
