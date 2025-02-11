<?php

use App\Modules\Asset\Models\Asset;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAssetInvestorData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Asset::chunk(100, function ($assets) {
            foreach ($assets as $asset) {
                // Ensure there is an investor_id before inserting.
                if ($asset->investor_id) {
                    DB::table('asset_investor')->insert([
                        'asset_id'   => $asset->id,
                        'investor_id'=> $asset->investor_id,
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('asset_investor')->truncate();
    }
}
