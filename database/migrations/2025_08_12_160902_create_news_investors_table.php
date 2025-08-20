<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsInvestorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_investors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('news_id');
            $table->unsignedBigInteger('investor_id');
            $table->timestamps();

            $table->foreign('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreign('investor_id')->references('id')->on('investors')->onDelete('cascade');
            $table->unique(['news_id', 'investor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_investors');
    }
}
