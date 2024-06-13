<?php

namespace App\Modules\Admin\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json = File::get('app/Modules/Admin/Database/Seeds/json/countries.json');
        $countries = json_decode($json, true);

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(
                ['country' => $country['country']],
                ['prefix' => $country['prefix']]
            );
        }    }
}
