<?php

namespace App\Modules\Admin\Database\Seeds;

use App\Models\Permission;
use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $roles = [
            [ 'name'  => 'administrator', 'guard_name' => 'admin' ],
        ];

        foreach($roles as $role) {
            if (!Role::where('name', $role['name'])->exists()) {
                Role::create($role);
            }
        }

        $allRoles = Role::where('name', 'administrator')->get();

        $user = Admin::where('email',  config('admin.admin_user_name'))->first();

        if (!is_null($user)) {

            foreach($allRoles as $rl) {

                $rolName = $rl['name'];

                foreach(Permission::where('guard_name', 'admin')->get() as $perms) {
                    if ( ! $rl->hasPermissionTo($perms->name) ) {
                        $perms->assignRole($rl);
                    }
                }

                if(!$user->hasRole($rolName)) {
                    $user->assignRole($rolName);
                }

            }

        }

    }

}
