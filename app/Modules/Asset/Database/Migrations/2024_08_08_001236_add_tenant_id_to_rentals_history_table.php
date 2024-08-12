<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTenantIdToRentalsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_payments_histories', function (Blueprint $table) {
            $table->integer('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rental_payments_histories', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });
    }
}
