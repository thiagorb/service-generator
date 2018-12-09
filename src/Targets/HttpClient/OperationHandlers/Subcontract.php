<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandler;

class Subcontract extends AbstractSubcontractMessage implements OperationHandler
{
    protected function getSubcontractNameExpr(): Expr
    {
        return new String_($this->decamelize($this->methodContext->getMethod()->getName()));
    }

    protected function decamelize(string $string): string
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string) ?: '');
    }
}
