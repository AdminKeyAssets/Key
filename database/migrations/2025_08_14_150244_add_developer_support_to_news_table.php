<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDeveloperSupportToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            // Add developer_id field for developer-created news
            $table->unsignedBigInteger('developer_id')->nullable()->after('admin_id');
            
            // Add foreign key for developer
            $table->foreign('developer_id')->references('id')->on('developers');
        });
        
        // Make admin_id nullable using raw SQL to avoid Doctrine enum issues
        DB::statement('ALTER TABLE news MODIFY admin_id BIGINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropForeign(['developer_id']);
            $table->dropColumn('developer_id');
        });
        
        DB::statement('ALTER TABLE news MODIFY admin_id BIGINT UNSIGNED NOT NULL');
    }
}
