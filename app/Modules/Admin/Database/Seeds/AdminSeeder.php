<?php

namespace App\Modules\Admin\Database\Seeds;

use App\Modules\Admin\Models\User\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $administrator = [
            [
                'name' => 'Administrator',
                'email' => config('admin.admin_user_name'),
                'password' => bcrypt(config('admin.admin_user_password')),
            ]
        ];

        foreach($administrator as $admin) {
            Admin::updateOrCreate([
                'email' => $admin['email']
            ],
                [
                    'name'  => 'Administrator',
                    'password'  => $admin['password']
                ]);

        }

    }

}
