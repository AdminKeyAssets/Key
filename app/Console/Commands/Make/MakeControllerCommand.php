<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeControllerCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'makeModule:controller {name : The name of the controller class}
    {--key=}
    {--repository=}
    {--base_controller=}
    {--create_request=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a controller';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return resource_path('stubs/controller.stub');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return \Illuminate\Console\GeneratorCommand
     */
    protected function replaceNamespace(&$stub, $name)
    {

        $stub = str_replace(
            ['DummyNamespace', 'DummyModuleKey', 'DummyModuleRepository', 'DummyBaseController', 'DummyCreateRequest'],
            [
                $this->getNamespace($name),
                $this->option('key'),
                str_replace( '-', '\\',$this->option('repository')),
                str_replace( '-', '\\',$this->option('base_controller')),
                str_replace( '-', '\\',$this->option('create_request')),
            ],
            $stub
        );

        return $this;
    }

}
