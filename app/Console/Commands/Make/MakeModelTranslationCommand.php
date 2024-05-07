<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class MakeModelTranslationCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'makeModule:modelTranslation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new a model translation';

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
        return resource_path('stubs/model.translation.stub');
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
