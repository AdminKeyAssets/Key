<?php

namespace App\Modules\News\Database\Seeds;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class NewsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get permission configuration for news
        $newsPermissions = config('permission_list.news.default');
        
        foreach ($newsPermissions as $permissionType => $permissionConfig) {
            $permissionKey = str_replace('{module_name}', 'news', $permissionConfig['key']);
            $permissionLabel = str_replace('{module_name}', 'News', $permissionConfig['label']);

            // Create permission if it doesn't exist
            if (!Permission::where('name', $permissionKey)->where('guard_name', 'admin')->exists()) {
                Permission::create([
                    'name' => $permissionKey,
                    'guard_name' => 'admin',
                    'label' => $permissionLabel
                ]);
            }
        }

        // Assign all news permissions to administrator role
        $adminRole = Role::where('name', 'administrator')->where('guard_name', 'admin')->first();
        
        if ($adminRole) {
            $newsPermissionNames = [];
            foreach ($newsPermissions as $permissionConfig) {
                $newsPermissionNames[] = str_replace('{module_name}', 'news', $permissionConfig['key']);
            }

            foreach ($newsPermissionNames as $permissionName) {
                $permission = Permission::where('name', $permissionName)->where('guard_name', 'admin')->first();
                if ($permission && !$adminRole->hasPermissionTo($permission)) {
                    $adminRole->givePermissionTo($permission);
                }
            }
        }
    }
}
