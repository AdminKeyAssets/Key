<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenovationStatusToAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('renovation_status')->nullable();
        });

        DB::table('assets')
            ->whereNotNull('renovation_agreement_date')
            ->update(['renovation_status' => 'In Progress']);

        DB::table('assets')
            ->whereNull('renovation_agreement_date')
            ->update(['renovation_status' => 'Completed']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('renovation_status');
        });
    }
}
