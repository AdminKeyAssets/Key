<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAssetFieldsMakeThemNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            $table->string('price')->nullable()->change();
            $table->string('agreement_status')->nullable()->change();
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
            $table->string('type')->nullable(false)->change();
            $table->string('price')->nullable(false)->change();
            $table->string('agreement_status')->nullable(false)->change();
        });
    }
}
