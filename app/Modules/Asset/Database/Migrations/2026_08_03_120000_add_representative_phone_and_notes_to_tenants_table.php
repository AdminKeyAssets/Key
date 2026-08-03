<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepresentativePhoneAndNotesToTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('representative_prefix')->nullable()->after('representative');
            $table->string('representative_phone')->nullable()->after('representative_prefix');
            $table->text('notes')->nullable()->after('monthly_rent');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['representative_prefix', 'representative_phone', 'notes']);
        });
    }
}
