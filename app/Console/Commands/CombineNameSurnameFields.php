<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CombineNameSurnameFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'names:combine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the migration to combine name and surname fields into full_name';

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
     * @return int
     */
    public function handle()
    {
        $this->info('Starting migration to combine name and surname fields...');
        
        // Run the migration that adds full_name field
        $this->call('migrate', [
            '--path' => 'database/migrations/2025_06_03_000001_combine_name_surname_fields.php'
        ]);
        
        $this->info('Initial migration completed. Testing period should now begin.');
        $this->info('After thorough testing, run "php artisan names:finalize" to remove the old columns.');
        
        return 0;
    }
}
