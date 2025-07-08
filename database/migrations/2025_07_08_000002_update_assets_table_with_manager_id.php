<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateAssetsTableWithManagerId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('admin_id');
            $table->foreign('manager_id')->references('id')->on('admins');
        });

        // Migrate existing data from asset_managers pivot table to the new column
        $assetManagers = DB::table('asset_managers')->get();
        foreach ($assetManagers as $relation) {
            // For each asset, use the first assigned manager as the single manager
            $existingManager = DB::table('assets')
                ->where('id', $relation->asset_id)
                ->whereNull('manager_id')
                ->update(['manager_id' => $relation->admin_id]);
        }
        
        // If asset has no manager assigned in pivot table, use the admin_id as manager_id
        DB::statement('UPDATE assets SET manager_id = admin_id WHERE manager_id IS NULL');
        
        // Drop the pivot table
        Schema::dropIfExists('asset_managers');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Recreate the pivot table
        Schema::create('asset_managers', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('admin_id');
            $table->primary(['asset_id', 'admin_id']);
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        // Migrate data back to the pivot table
        DB::table('assets')->whereNotNull('manager_id')->each(function ($asset) {
            DB::table('asset_managers')->insert([
                'asset_id' => $asset->id,
                'admin_id' => $asset->manager_id
            ]);
        });

        // Drop the manager_id column
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
}
