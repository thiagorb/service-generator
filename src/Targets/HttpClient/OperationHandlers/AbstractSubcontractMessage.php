<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;
use Thiagorb\ServiceGenerator\Targets\HttpClient\MethodContext;
use Thiagorb\ServiceGenerator\Targets\HttpClient\Factory;

abstract class AbstractSubcontractMessage
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var MethodContext
     */
    protected $methodContext;

    public function __construct(Factory $factory, MethodContext $methodContext)
    {
        $this->factory = $factory;
        $this->methodContext = $methodContext;
    }

    public function buildBody(): string
    {
        return $this->factory->makeFormatter()->prettyPrint(
            [
                new Return_(
                    new MethodCall(
                        new Variable('this'),
                        'createSubcontract',
                        [
                            new Arg($this->getSubcontractNameExpr()),
                            new Arg(new String_($this->getSubcontractClassName()))
                        ]
                    )
                )
            ]
        );
    }

    abstract protected function getSubcontractNameExpr(): Expr;

    protected function getSubcontractClassName(): string
    {
        $returnType = $this->methodContext->getMethod()->getReturnType();

        if (!$returnType instanceof InterfaceType) {
            throw new \Error('Invaild subcontract type');
        }

        return $returnType->getFullName();
    }

    public function buildMetaData(): ?array
    {
        return null;
    }
}