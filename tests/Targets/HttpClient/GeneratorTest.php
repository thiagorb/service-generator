<?php

namespace Fake\Contracts
{
    class Model
    {
        public function __construct()
        {
        }
    }

    interface Contract
    {
        public function fakeMethod(string $fakeParameter): string;
        public function fakeMethodInt(int $fakeParameter = 0): string;
        public function fakeMethodIntNull(int $fakeParameter = null): string;
        public function fakeMethodNullable(?string $fakeParameter): ?string;
        public function fakeMethodNull(string $fakeParameter = null): string;
        public function fakeMethodClass(Model $fakeParameter): Model;
        public function fakeMethodNullableClass(?Model $fakeParameter): ?Model;
    }
}

namespace Tests\Targets\HttpClient
{
    use Fake\Contracts\Contract;
    use Thiagorb\ServiceGenerator\Configuration\Service as ServiceConfiguration;
    use Thiagorb\ServiceGenerator\Definitions\File;
    use Thiagorb\ServiceGenerator\Targets\HttpClient\Generator;
    use Thiagorb\ServiceGenerator\TypeResolver;
    use Tests\TestCase;

    class GeneratorTest extends TestCase
    {
        public function testGenerate()
        {
            $generator = new Generator();

            /** @var File[] $files */
            $files = iterator_to_array(
                $generator->generate(
                    new ServiceConfiguration(
                        Contract::class,
                        new Generator(),
                        '/fake_path',
                        'Fake\\Client'
                    ),
                    new TypeResolver()
                ),
                false
            );

            $this->assertCount(3, $files);
            $this->assertEquals('/fake_path/Implementations/Contract.php', $files[0]->getPath());
            $this->assertEquals('/fake_path/Transformers/Fake/Contracts/ModelTransformer.php', $files[1]->getPath());
            $this->assertEquals('/fake_path/Service.php', $files[2]->getPath());
            eval('?>' . $files[0]->getContent());
            eval('?>' . $files[1]->getContent());
            eval('?>' . $files[2]->getContent());
            $this->assertTrue(class_exists(\Fake\Client\Implementations\Contract::class));
            $this->assertTrue(class_exists(\Fake\Client\Service::class));

        }
    }
}
