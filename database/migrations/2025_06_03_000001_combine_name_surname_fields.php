<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CombineNameSurnameFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add full_name column to admins table
        Schema::table('admins', function (Blueprint $table) {
            $table->string('full_name')->after('surname')->nullable();
        });

        // Combine name and surname for admins
        DB::table('admins')->select('id', 'name', 'surname')->get()->each(function ($admin) {
            DB::table('admins')
                ->where('id', $admin->id)
                ->update(['full_name' => trim($admin->name . ' ' . $admin->surname)]);
        });

        // Add full_name column to investors table
        Schema::table('investors', function (Blueprint $table) {
            $table->string('full_name')->after('surname')->nullable();
        });

        // Combine name and surname for investors
        DB::table('investors')->select('id', 'name', 'surname')->get()->each(function ($investor) {
            DB::table('investors')
                ->where('id', $investor->id)
                ->update(['full_name' => trim($investor->name . ' ' . $investor->surname)]);
        });

        // Make name and surname nullable for now (we'll remove them in a later migration after ensuring everything works)
        Schema::table('admins', function (Blueprint $table) {
            $table->string('name', 100)->nullable()->change();
            $table->string('surname', 100)->nullable()->change();
        });

        Schema::table('investors', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('surname')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Restore name and surname as required fields
        Schema::table('admins', function (Blueprint $table) {
            $table->string('name', 100)->nullable(false)->change();
        });

        Schema::table('investors', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('surname')->nullable(false)->change();
        });

        // Remove full_name columns
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });
    }
}
