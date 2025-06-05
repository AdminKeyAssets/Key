<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FinalizeNameSurnameFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'names:finalize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the old name and surname fields after a successful transition to full_name';

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
        if (!$this->confirm('This will remove the name and surname fields from Admin and Investor tables. Are you sure you want to proceed?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Running final migration to remove name and surname fields...');
        
        // Run the migration that removes the old fields
        $this->call('migrate', [
            '--path' => 'database/migrations/2025_06_04_000001_remove_name_surname_fields.php'
        ]);
        
        $this->info('Migration completed. The name and surname fields have been removed.');
        
        return 0;
    }
}
