<?php


namespace App\Console\Commands\Make;


use Illuminate\Console\GeneratorCommand;

class MakeFacadeCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:facade
    	{name : The name of the facade class} 
    	{--class=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a facade';

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
        return resource_path('stubs/facade.stub');
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
            ['DummyNamespace', 'DummyFacadeName'],
            [$this->getNamespace($name),  $this->option('class')],
            $stub
        );

        return $this;
    }

}
