<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeModelCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'makeModule:model 
    {name : The name of the controller class}
    {--base_model=}
    {--table=}
    {--translate_model=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a model';

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
        return resource_path('stubs/model.stub');
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
            ['DummyNamespace', 'DummyTranslatableModel', 'DummyBaseModel','DummyTableName'],
            [
                $this->getNamespace($name),
                str_replace( '-', '\\',$this->option('translate_model')),
                str_replace( '-', '\\',$this->option('base_model')),
                $this->option('table')
            ],
            $stub
        );

        return $this;
    }


}
