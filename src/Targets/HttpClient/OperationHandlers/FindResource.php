<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class FindResource extends AbstractSubcontractMessage implements OperationHandler
{
    protected function getSubcontractNameExpr(): Expr
    {
        return new Variable(array_values($this->methodContext->getMethod()->getParameters())[0]->getName());
    }
}
