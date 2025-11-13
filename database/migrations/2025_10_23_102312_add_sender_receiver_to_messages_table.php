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
        // Make operations idempotent / safe: check columns before changing
        if (Schema::hasTable('messages')) {
            if (Schema::hasColumn('messages', 'user_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->renameColumn('user_id', 'sender_id');
                });
            }

            if (!Schema::hasColumn('messages', 'receiver_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    // Yeni receiver_id sütunu ekle
                    $table->foreignId('receiver_id')
                        ->constrained('users')
                        ->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('messages')) {
            if (Schema::hasColumn('messages', 'receiver_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->dropForeign(['receiver_id']);
                    $table->dropColumn('receiver_id');
                });
            }

            if (Schema::hasColumn('messages', 'sender_id')) {
                Schema::table('messages', function (Blueprint $table) {
                    $table->renameColumn('sender_id', 'user_id');
                });
            }
        }
    }
};
