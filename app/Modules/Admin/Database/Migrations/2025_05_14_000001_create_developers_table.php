<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevelopersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Developer Name
            $table->string('id_code')->unique(); // ID Code
            $table->string('representative');    // Representative name
            $table->string('tel');               // Telephone
            $table->string('representative_position'); // Representative Position
            $table->string('service_agreement')->nullable(); // Service Agreement file path
            $table->string('logo')->nullable();  // Logo file path
            $table->string('stamp')->nullable(); // Stamp file path
            $table->string('signature')->nullable(); // Signature file path
            $table->string('username')->unique(); // Username for login
            $table->string('password');          // Password
            $table->rememberToken();
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
        Schema::dropIfExists('developers');
    }
}
