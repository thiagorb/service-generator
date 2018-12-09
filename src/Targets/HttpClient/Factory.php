<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Thiagorb\ServiceGenerator\Targets\HttpClient\ContractHandlers\DefaultContract;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\CreateResource;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\ReadResource;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\UpdateResource;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\DeleteResource;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\FindResource;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\ProcedureCall;
use Thiagorb\ServiceGenerator\Targets\HttpClient\OperationHandlers\Subcontract;
use Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Expr\Array_;
use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\TypeResolver;

class Factory
{
    /**
     * @var ServiceConfiguration
     */
    protected $serviceConfiguration;

    /**
     * @var TypeResolver
     */
    protected $typeResolver;

    /**
     * @var TransformerResolver
     */
    protected $transformerResolver;

    public function __construct(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver)
    {
        $this->serviceConfiguration = $serviceConfiguration;
        $this->typeResolver = $typeResolver;
        $this->transformerResolver = new TransformerResolver($serviceConfiguration, $typeResolver);
    }

    public function makeContractHandler(ContractContext $contractContext): ContractHandler
    {
        return new DefaultContract($this, $contractContext);
    }

    public function makeClientBuilder(ContractContext $contractContext): ClientBuilder
    {
        return new ClientBuilder($this, $contractContext);
    }

    public function makeOperationHandler(MethodContext $methodContext): OperationHandler
    {
        if ($methodContext->getMethod()->getReturnType() instanceof InterfaceType) {
            if ($methodContext->getMethod()->getName() === 'find' && count($methodContext->getMethod()->getParameters()) === 1) {
                return new FindResource($this, $methodContext);
            }

            return new Subcontract($this, $methodContext);
        }

        switch ($methodContext->getMethod()->getName()) {
            case 'create': return new CreateResource($this, $methodContext);
            case 'read': return new ReadResource($this, $methodContext);
            case 'update': return new UpdateResource($this, $methodContext);
            case 'delete': return new DeleteResource($this, $methodContext);
            default: return new ProcedureCall($this, $methodContext);
        }
    }

    public function getTransformerResolver(): TransformerResolver
    {
        return $this->transformerResolver;
    }

    public function makeFormatter(): Standard
    {
        /**
         * @psalm-suppress PropertyNotSetInConstructor
         */
        return new class extends Standard
        {
            /**
             * @param Array_ $node
             *
             * @return string
             */
            protected function pExpr_Array(Array_ $node)
            {
                /**
                 * @psalm-suppress InvalidArgument
                 */
                return '[' . $this->pCommaSeparatedMultiline($node->items, true) . ($node->items ? $this->nl : '') . ']';
            }
        };
    }
}