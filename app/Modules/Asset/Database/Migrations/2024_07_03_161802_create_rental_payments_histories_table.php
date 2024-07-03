<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalPaymentsHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rental_payments_histories', function (Blueprint $table) {
            $table->id();
            $table->string('date');
            $table->decimal('amount', 10, 2);
            $table->string('attachment')->nullable();
            $table->string('currency')->default('USD');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_payments_histories');
    }
}
