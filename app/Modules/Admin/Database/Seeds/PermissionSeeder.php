<?php

namespace App\Modules\Admin\Database\Seeds;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Psy\Util\Str;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $permissionList = config('permission_list');

        foreach($permissionList as $moduleKey => $permissions) {

            foreach($permissions as $key => $permission) {

                // If default permission, add this.
                if ($key == 'default') {

                    foreach($permission as $somePermission) {

                        Permission::updateOrCreate([
                            'name'          =>  str_replace('{module_name}', $moduleKey, $somePermission['key']),
                            'guard_name'    => 'admin',
                        ],
                        [
                            'label'     => str_replace('{module_name}', ucfirst($moduleKey), $somePermission['label'])
                        ]);

                    }

                    continue;

                }

                Permission::updateOrCreate([
                    'name'          => $permission['key'],
                    'guard_name'    => 'admin',
                ],
                [
                    'label'     => $permission['label']
                ]);

            }

        }

    }

}
