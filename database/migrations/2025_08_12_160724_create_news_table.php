<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            $table->unsignedBigInteger('admin_id'); // Creator
            $table->unsignedBigInteger('manager_id')->nullable(); // Assigned manager (only for admin-created news)
            $table->enum('created_by_type', ['admin', 'developer', 'manager'])->default('admin');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('admin_id')->references('id')->on('admins');
            $table->foreign('manager_id')->references('id')->on('admins');
            $table->index(['status', 'published_at']);
            $table->index(['admin_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
