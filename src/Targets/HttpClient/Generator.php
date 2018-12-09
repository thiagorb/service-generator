<?php

namespace Thiagorb\ServiceGenerator\Targets\HttpClient;

use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
use Thiagorb\ServiceGenerator\Targets\GeneratorInterface;
use Thiagorb\ServiceGenerator\TypeResolver;
use Thiagorb\ServiceGenerator\ContractInference;

class Generator implements GeneratorInterface
{
    protected function makeFactory(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver): Factory
    {
        return new Factory($serviceConfiguration, $typeResolver);
    }

    public function generate(ServiceConfiguration $serviceConfiguration, TypeResolver $typeResolver): \Traversable
    {
        $factory = $this->makeFactory($serviceConfiguration, $typeResolver);
        $contracts = [];

        $builtDefinitions = [];
        $queue = [$serviceConfiguration->getEntryPointContract()];
        while ($queue) {
            $contractDefinition = (new ContractInference($serviceConfiguration->getContractsNamespace(), array_shift($queue), $typeResolver))->buildDefinition();
            $contractHandler = $factory->makeContractHandler(new ContractContext($serviceConfiguration, $contractDefinition));
            yield from $contractHandler->generate();
            $contracts[$contractDefinition->getFullClassName()] = $contractHandler->getMetaData();

            foreach ($contractHandler->getSubcontracts() as $subContract) {
                if (isset($builtDefinitions[$subContract])) {
                    continue;
                }

                $builtDefinitions[$subContract] = true;
                array_push($queue, $subContract);
            }
        }

        $metaData = ['contracts' => $contracts];

        yield from $factory->getTransformerResolver()->generate();

        yield $this->makeServiceBuilder($serviceConfiguration, $metaData)->build();
    }

    protected function makeServiceBuilder(ServiceConfiguration $serviceConfiguration, array $metaData): ServiceBuilder
    {
        return new ServiceBuilder($serviceConfiguration, $metaData);
    }
}