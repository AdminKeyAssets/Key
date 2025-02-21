<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRenovationFieldsToAssets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('renovation_agreement_date')->nullable();
            $table->string('renovation_first_payment_date')->nullable();
            $table->string('renovation_agreement')->nullable();
            $table->double('renovation_total_price')->nullable();
            $table->integer('renovation_period')->nullable();
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
            $table->dropColumn('renovation_agreement_date');
            $table->dropColumn('renovation_first_payment_date');
            $table->dropColumn('renovation_agreement');
            $table->dropColumn('renovation_total_price');
            $table->dropColumn('renovation_period');
        });
    }
}
