<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeRepositoryInterfaceCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'makeModule:repositoryInterface';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a repository interface';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'interface';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return resource_path('stubs/repository.interface.stub');
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

}
