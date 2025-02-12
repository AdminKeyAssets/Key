<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRenovationPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('renovation_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_date');
            $table->double('amount');
            $table->boolean('status')->default(false);
            $table->string('currency')->default('USD');
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->double('left_amount');
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
        Schema::dropIfExists('renovation_payments');
    }
}
