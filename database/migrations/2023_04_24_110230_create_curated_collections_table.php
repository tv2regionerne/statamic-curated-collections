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
        Schema::create('curated_collections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('handle')->index();
            $table->string('site')->default('default');
            $table->json('collections')->nullable();
            $table->integer('max_items')->nullable();
            $table->boolean('display_form')->default(false);
            $table->string('fallback_collection')->nullable();
            $table->string('fallback_sort_field')->default('date')->nullable();
            $table->string('fallback_sort_direction')->default('desc');
            $table->boolean('automation')->default(true);
            $table->boolean('update_expiration_on_publish')->default(true);
            $table->integer('expiration_time')->default(96);
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
        Schema::dropIfExists('curated_collections');
    }
};
