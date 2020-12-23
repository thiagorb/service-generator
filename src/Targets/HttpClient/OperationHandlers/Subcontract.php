<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class Subcontract extends AbstractSubcontractMessage implements OperationHandler
{
    protected function getSubcontractNameExpr(): Expr
    {
        return new String_($this->methodContext->getNamingConvention()->transformMethodName($this->methodContext->getMethod()));
    }
}
