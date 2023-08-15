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
        Schema::create('curated_collection_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site')->index()->default('default');
            $table->foreignUuid('curated_collection_id')->constrained('curated_collections')->onDelete('cascade');
            $table->string('collection')->index();
            $table->string('entry_id')->index();
            $table->json('data')->nullable();
            $table->string('status')->index();
            $table->integer('order_column')->nullable();
            $table->integer('publish_order')->nullable();
            $table->integer('expiration_time')->nullable();
            $table->timestamp('unpublish_at')->nullable();
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
        Schema::dropIfExists('curated_collection_entries');
    }
};
