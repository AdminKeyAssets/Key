<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeRepositoryCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
//    protected $name = 'makeModule:repository';

    protected $signature = 'makeModule:repository
    	{name : The name of the controller class}
    	{--model_namespace=} 
    	{--model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a repository';

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
        return resource_path('stubs/repository_new.stub');
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
            ['DummyNamespace', 'DummyBaseModelNamespace', 'DummyBaseModel'],
            [$this->getNamespace($name),  str_replace( '-', '\\',$this->option('model_namespace')), str_replace( '-', '\\',$this->option('model'))],
            $stub
        );

        return $this;
    }

}
