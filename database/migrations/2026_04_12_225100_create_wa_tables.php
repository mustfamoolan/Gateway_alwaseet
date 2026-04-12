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
        // Table for WhatsApp Projects (Accounts)
        Schema::create('wa_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name'); // Project Name
            $table->string('owner_name'); // Person Name
            $table->string('phone_number')->nullable(); // Linked Phone Number
            $table->string('api_key')->unique();
            $table->enum('status', ['pending', 'connected', 'disconnected'])->default('pending');
            $table->text('session_data')->nullable(); // Store Baileys session if needed locally
            $table->timestamps();
        });

        // Table for Logging Messages
        Schema::create('wa_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_project_id')->constrained('wa_projects')->onDelete('cascade');
            $table->string('to_number');
            $table->text('message_body');
            $table->enum('status', ['sent', 'failed', 'delivered', 'read'])->default('sent');
            $table->text('error_message')->nullable();
            $table->json('response_metadata')->nullable(); // Full response from Baileys
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_messages');
        Schema::dropIfExists('wa_projects');
    }
};
