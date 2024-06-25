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
            $table->string('project_name');
            $table->string('project_description');
            $table->string('project_link');
            $table->string('location')->nullable();
            $table->string('type');
            $table->string('floor');
            $table->string('flat_number');
            $table->double('price');
            $table->string('condition');
            $table->string('city');
            $table->string('agreement_status');
            $table->text('address');
            $table->string('delivery_date')->nullable();
            $table->string('agreement_date')->nullable();
            $table->string('first_payment_date')->nullable();
            $table->double('area')->nullable();
            $table->integer('period')->nullable();
            $table->double('total_price')->nullable();
            $table->double('current_value')->nullable();
            $table->double('current_value')->nullable();
            $table->double('total_agreement_price')->nullable();
            $table->string('cadastral_number')->nullable();
            $table->string('document')->nullable();
            $table->string('floor_plan')->nullable();
            $table->string('flat_plan')->nullable();
            $table->string('agreement')->nullable();
            $table->string('ownership_certificate')->nullable();
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
