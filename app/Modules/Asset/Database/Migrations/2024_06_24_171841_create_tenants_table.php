<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('surname');
            $table->string('id_number');
            $table->string('citizenship');
            $table->string('email');
            $table->string('phone');
            $table->string('prefix');
            $table->string('agreement_date');
            $table->string('agreement_term');
            $table->decimal('monthly_rent', 15, 2);
            $table->string('currency');
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('tenants');
    }
}
