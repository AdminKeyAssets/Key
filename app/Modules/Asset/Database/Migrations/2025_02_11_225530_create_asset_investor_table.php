<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetInvestorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_investor', function (Blueprint $table) {
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('investor_id');

            // Define foreign keys
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('investor_id')->references('id')->on('investors')->onDelete('cascade');

            // Optionally, set a composite primary key
            $table->primary(['asset_id', 'investor_id']);
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_investor');
    }
}
