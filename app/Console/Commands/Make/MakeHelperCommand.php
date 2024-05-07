<?php


namespace App\Console\Commands\Make;


use Illuminate\Console\GeneratorCommand;

class MakeHelperCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'makeModule:helper
    {name : The name of the helper class}
    {--key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a helper';

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
        return resource_path('stubs/helper.stub');
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
            ['DummyNamespace', 'DummyKey'],
            [
                $this->getNamespace($name),
                $this->option('key')
            ],
            $stub
        );

        return $this;
    }

}
