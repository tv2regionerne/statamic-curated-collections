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
        Schema::table('curated_collection_entries', function (Blueprint $table) {
            $table->timestamp('priority_date')->nullable()->after('unpublish_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curated_collection_entries', function (Blueprint $table) {
            $table->dropColumn('priority_date');
        });
    }
};
