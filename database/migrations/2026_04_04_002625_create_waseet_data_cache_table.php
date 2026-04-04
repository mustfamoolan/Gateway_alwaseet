<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('waseet_data_cache', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // city, region, package_size
            $table->integer('external_id');
            $table->integer('parent_id')->nullable(); // for region city_id
            $table->string('name');
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waseet_data_cache');
    }
};
