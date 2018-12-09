<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Thiagorb\ServiceGenerator\Definitions\Parameter;
use Thiagorb\ServiceGenerator\Definitions\Types\VoidType;
use Thiagorb\ServiceGenerator\Targets\HttpClient\MethodContext;
use Thiagorb\ServiceGenerator\Targets\HttpClient\Factory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Arg;

abstract class AbstractResourceMessage
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
        $parameters = new Array_(
            array_map(
                function (Parameter $parameter): ArrayItem {
                    return new ArrayItem(
                        new Variable($parameter->getName()),
                        new String_($parameter->getName())
                    );
                },
                $this->methodContext->getMethod()->getParameters()
            )
        );

        $processMessageCall = new MethodCall(
            new Variable('this'),
            'processMessage',
            [new Arg(new String_($this->methodContext->getMethod()->getName())), new Arg($parameters)]
        );

        $statements = [
            $this->methodContext->getMethod()->getReturnType() instanceof VoidType
            ? new Expression($processMessageCall)
            : new Return_($processMessageCall)
        ];

        return $this->factory->makeFormatter()->prettyPrint($statements);
    }

    public function buildMetaData(): ?array
    {
        return [
            'relative_path' => '',
            'http_method' => $this->getHttpMethod(),
            'parameters' => $this->buildMethodParametersMetaData(),
            /** @todo: implement exceptions inference */
            'exceptions' => [],
            'return_type' => $this->factory->getTransformerResolver()->transform($this->methodContext->getMethod()->getReturnType()),
        ];
    }

    protected function buildMethodParametersMetaData(): array
    {
        $parameters = [];

        foreach ($this->methodContext->getMethod()->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $this->buildParameterMetaData($parameter);
        }

        return $parameters;
    }

    protected function buildParameterMetaData(Parameter $parameter): array
    {
        return $this->factory->getTransformerResolver()->transform($parameter->getType());
    }

    protected abstract function getHttpMethod(): string;
}