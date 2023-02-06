<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('number');
            $table->unsignedInteger('parent');
            $table->unsignedInteger('order');
            $table->string('title',128)->nullable();
            $table->string('status',32)->nullable();
            $table->jsonb('tags')->nullable();
            $table->string('author',128);
            $table->text('text');
            $table->jsonb('files');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['deleted_at','number']);
            $table->index(['deleted_at','parent','number']);
            $table->fullText('text');//full text index
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
