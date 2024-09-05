<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppliedToMonthsFieldToRentalPaymentHistories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rental_payments_histories', function (Blueprint $table) {
            $table->json('applied_to_months')->nullable();
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
            $table->dropColumn('applied_to_months');
        });
    }
}
