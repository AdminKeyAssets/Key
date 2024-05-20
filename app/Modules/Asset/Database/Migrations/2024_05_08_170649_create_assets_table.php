<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('investor_id')->nullable();
            $table->string('icon')->nullable();
            $table->string('name');
            $table->string('city');
            $table->text('address');
            $table->string('delivery_date')->nullable();
            $table->double('area')->nullable();
            $table->double('total_price')->nullable();
            $table->string('cadastral_number')->nullable();
            $table->string('document')->nullable();
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
        Schema::dropIfExists('assets');
    }
}
