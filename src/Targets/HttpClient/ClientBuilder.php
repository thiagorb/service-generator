<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Thiagorb\ServiceGenerator\Configuration\Service;
use Thiagorb\ServiceGenerator\Definitions\ClassFile;
use Thiagorb\ServiceGenerator\Definitions\Contract as ContractDefinition;
use Thiagorb\ServiceGenerator\Definitions\Method as MethodDefinition;
use Thiagorb\ServiceGenerator\Definitions\Parameter as ParameterDefinition;
use Thiagorb\ServiceGenerator\Definitions\Types\BaseType;
use Thiagorb\ServiceGenerator\Definitions\Types\Visitor;
use Thiagorb\ServiceGeneratorRuntime\HttpTransport;

class ClientBuilder
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

    public function build(string $targetNamespace, string $className): string
    {
        $file = new PhpFile;
        $namespace = $file->addNamespace($targetNamespace);
        $class = $namespace
            ->addClass($className)
            ->setType(ClassType::TYPE_CLASS)
            ->addExtend(\Thiagorb\ServiceGeneratorRuntime\BaseService::class)
            ->addImplement($this->contractContext->getContract()->getFullClassName());

        //$this->declareConstructor($namespace, $class);

        $meta = ['methods' => []];

        /** @var MethodDefinition $methodDefinition */
        foreach ($this->contractContext->getContract()->getMethods() as $methodDefinition) {
            $this->addUse($namespace, $methodDefinition->getReturnType());
            $operationHandler = $this->factory->makeOperationHandler($this->contractContext->withMethod($methodDefinition));
            $method = $class->addMethod($methodDefinition->getName())
                ->addComment(Helpers::unformatDocComment($methodDefinition->getComment() ?? ''))
                ->setReturnType($methodDefinition->getTypeHintReturnType()['type'])
                ->setReturnNullable($methodDefinition->getTypeHintReturnType()['nullable'])
                ->setVisibility(ClassType::VISIBILITY_PUBLIC)
                ->setBody($operationHandler->buildBody());

            if ($metaData = $operationHandler->buildMetaData()) {
                $meta['methods'][$method->getName()] = $metaData;
            }

            /** @var ParameterDefinition $parameterDefinition */
            foreach ($methodDefinition->getParameters() as $parameterDefinition) {
                $this->addUse($namespace, $parameterDefinition->getType());
                $parameter = $method->addParameter($parameterDefinition->getName())
                    ->setTypeHint($parameterDefinition->getTypeHintType()['type'])
                    ->setNullable($parameterDefinition->getTypeHintType()['nullable']);

                if ($parameterDefinition->hasDefaultValue()) {
                    $parameter->setDefaultValue($parameterDefinition->getDefaultValue());
                }
            }
        }

        $class->addProperty('meta')
            ->setComment(PHP_EOL . '@var array' . PHP_EOL)
            ->setVisibility('protected')
            ->setValue($meta);

        return (string)$file;
    }

    /*
    protected function declareConstructor(PhpNamespace $namespace, ClassType $class)
    {
        $namespace->addUse(HttpTransport::class);
        $constructor = $class->addMethod('__construct');

        $class->addProperty('transport')
            ->setComment(PHP_EOL . '@var HttpTransport' . PHP_EOL)
            ->setVisibility('protected');

        $constructor->addParameter('transport')->setTypeHint(HttpTransport::class);
        $constructor->setComment(
            $this->generateParameterDocBlock('HttpTransport', 'transport', 'Transport')
        );
        $constructor->setBody('$this->transport = $transport;');
    }

    protected function generateParameterDocBlock(string $type, string $name, string $comment): string
    {
        return sprintf('@param %s $%s %s', $type, $name, $comment);
    }
    */

    protected function addUse(PhpNamespace $namespace, BaseType $baseType)
    {
        $baseType->accept(new class ($namespace) extends Visitor
        {
            /**
             * @var PhpNamespace
             */
            protected $namespace;

            public function __construct(PhpNamespace $namespace)
            {
                $this->namespace = $namespace;
            }

            protected function addUse(string $type)
            {
                $this->namespace->addUse($type);
            }

            public function visitArray(\Thiagorb\ServiceGenerator\Definitions\Types\ArrayType $type)
            {
                $type->getItemType()->accept($this);
            }

            public function visitNullable(\Thiagorb\ServiceGenerator\Definitions\Types\NullableType $type)
            {
                $type->getInnerType()->accept($this);
            }

            public function visitClass(\Thiagorb\ServiceGenerator\Definitions\Types\ClassType $type)
            {
                $this->addUse($type->getFullName());
            }

            public function visitInterface(\Thiagorb\ServiceGenerator\Definitions\Types\InterfaceType $type)
            {
                $this->addUse($type->getFullName());
            }
        });
    }
}