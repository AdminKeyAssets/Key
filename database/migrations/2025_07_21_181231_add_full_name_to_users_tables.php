<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFullNameToUsersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add full_name column to admins table
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('surname');
            });
            
            // Update full_name column with concatenated name and surname
            DB::statement("UPDATE admins SET full_name = CONCAT(name, ' ', surname)");
        }

        // Add full_name column to investors table
        if (Schema::hasTable('investors')) {
            Schema::table('investors', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('surname');
            });
            
            // Update full_name column with concatenated name and surname
            DB::statement("UPDATE investors SET full_name = CONCAT(name, ' ', surname)");
        }

        // Add full_name column to leads table
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('full_name')->nullable()->after('surname');
            });
            
            // Update full_name column with concatenated name and surname
            DB::statement("UPDATE leads SET full_name = CONCAT(name, ' ', surname)");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove full_name column from admins table
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('full_name');
            });
        }

        // Remove full_name column from investors table
        if (Schema::hasTable('investors')) {
            Schema::table('investors', function (Blueprint $table) {
                $table->dropColumn('full_name');
            });
        }

        // Remove full_name column from leads table
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('full_name');
            });
        }
    }
}
