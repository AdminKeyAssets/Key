<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         $this->call(\App\Modules\Admin\Database\Seeds\AdminSeeder::class);
        $this->call(\App\Modules\Admin\Database\Seeds\PermissionSeeder::class);
        $this->call(\App\Modules\Admin\Database\Seeds\RoleSeeder::class);
    }
}
