<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNameSurnameFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration will be run after ensuring everything works with the full_name field
        
        // Remove name and surname columns from admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('surname');
        });

        // Remove name and surname columns from investors table
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('surname');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add name and surname columns back to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->string('name', 100)->after('id');
            $table->string('surname', 100)->nullable()->after('name');
        });

        // Add name and surname columns back to investors table
        Schema::table('investors', function (Blueprint $table) {
            $table->string('name')->after('phone');
            $table->string('surname')->after('name');
        });
    }
}
