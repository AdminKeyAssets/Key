<?php

namespace App\Console\Commands;

use App\Modules\Admin\Http\Controllers\BaseController;
use App\Modules\Admin\Models\BaseModel;
use Illuminate\Console\Command;
use Artisan;
use Str;
use \App\Console\Commands\Traits\Command as TraitCommand;

class MakeModuleCommand extends Command
{

    use TraitCommand;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'makeComponent {moduleName} {componentName} {--lang=} {--type=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $moduleName = $this->argument('moduleName');
        $componentName = $this->argument('componentName');

        $this->titleMessage('იქმნება მოდულის კომპონენტები - ' . $componentName);

        $this->warnMessage('App\Modules\\' . ucfirst($moduleName) . '\Models\\' . $componentName);
        $this->warnMessage('App\Modules\\' . ucfirst($moduleName) . '\Controller\\Admin\\' . $componentName);
        $this->warnMessage('App\Modules\\' . ucfirst($moduleName) . '\Request\\Admin\\' . $componentName . '\CreateRequest');
        $this->warnMessage(Str::lower($componentName) . ' migration');

        $isConfirm = $this->confirm('დარწმუნებული ხართ, რომ გსურთ შექმნა?');

        if (!$isConfirm) return;

        try {

            $typeNamespace = '';
            if ($this->option('type')) {
                $typeNamespace = $this->option('type');
            }

            $repositoryInterfaceClass = 'Modules/' . ucfirst($moduleName)  . '/Repositories/Contracts/I'.$componentName . 'Repository';
            $repositoryClass = 'Modules/' . ucfirst($moduleName)  . '/Repositories/Eloquent/'.$componentName . 'Repository';
            $modelClass = 'Modules/' . ucfirst($moduleName)  . '/Models/'. $typeNamespace . $componentName;
            $tableName = Str::plural(Str::lower($componentName));

            Artisan::call('makeModule:repository Modules/' . ucfirst($moduleName)  . '/Repositories/Eloquent/'. $typeNamespace . $componentName . 'Repository 
            --model_namespace=App-' . str_replace('/', "-", $modelClass) .' --model=' . $componentName);

            $facadeClass = 'Modules/' . ucfirst($moduleName) . '/Facades/Repositories/' . $typeNamespace . $componentName . 'RepositoryFacade';
            Artisan::call('make:facade ' . $facadeClass .
                ' --class=' .  $componentName . 'Repository'
            );

            // Create Models.
            if ($this->option('lang') == 1){

                $translatableModel = 'Modules/' . ucfirst($moduleName)  . '/Models/Translations/'.$componentName . 'Translation';

                // Models
                Artisan::call('makeModule:model ' . $modelClass . ' 
                --translate_model=-App-' . str_replace('/', "-", $translatableModel)
                    . ' --table=' . $tableName
                    . ' --base_model=-' .  str_replace('\\', "-", BaseModel::class));
                Artisan::call('makeModule:modelTranslation ' . $translatableModel);

            } else {
                Artisan::call('makeModule:empty-model ' . $modelClass .
                    ' --base_model=-' .  str_replace('\\', "-", BaseModel::class) .
                    ' --table=' . $tableName
                );
            }

            $requestClass = 'Admin/' . $componentName . '/CreateRequest';

            // Controller
            Artisan::call('makeModule:controller Modules/' . ucfirst($moduleName)  . '/Http/Controllers/Admin/'.$componentName . 'Controller '
                . ' --key=' . Str::lower($componentName)
                . ' --repository=App-' .str_replace('/', "-", $repositoryInterfaceClass)
                . ' --base_controller=-' . str_replace('\\', "-", BaseController::class)
                . ' --create_request=App-' . str_replace('/', "-", $requestClass)
            );

            // Request
            Artisan::call('make:module:request ' . $moduleName . ' ' . $requestClass);

            // Request
            Artisan::call('make:module:request ' . $moduleName . ' ' . $requestClass);

            //Helper
            Artisan::call('makeModule:helper   Modules/' . ucfirst($moduleName)  . '/Helper/'.$componentName . 'Helper '.
                ' --key=' .  Str::lower($componentName)
            );

            // Migration
            Artisan::call('make:module:migration ' . $moduleName . ' create_' . $componentName . '_table --create=' . $tableName);

        } catch (\Exception $ex) {
            $this->errorMessage($ex->getMessage());
            return;
        }

        $this->infoMessage('კომპონენტი წარმატებით შექიმნა');

    }

}
