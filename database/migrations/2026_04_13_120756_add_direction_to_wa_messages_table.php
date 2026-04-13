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
        Schema::table('wa_messages', function (Blueprint $table) {
            $table->string('from_number')->nullable()->after('wa_project_id');
            $table->enum('direction', ['outbound', 'inbound'])->default('outbound')->after('message_body');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_messages', function (Blueprint $table) {
            $table->dropColumn(['from_number', 'direction']);
        });
    }
};
