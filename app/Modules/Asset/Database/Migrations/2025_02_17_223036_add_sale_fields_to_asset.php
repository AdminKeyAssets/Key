<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleFieldsToAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('sale_price')->nullable();
            $table->string('purchaser')->nullable();
            $table->string('sale_agreement')->nullable();
            $table->string('sale_date')->nullable();
            $table->string('sale_status')->default('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('sale_price');
            $table->dropColumn('purchaser');
            $table->dropColumn('sale_agreement');
            $table->dropColumn('sale_date');
            $table->dropColumn('sale_status');
        });
    }
}
